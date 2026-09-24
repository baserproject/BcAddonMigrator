<?php
/**
 * baserCMS :  Based Website Development Project <https://basercms.net>
 * Copyright (c) NPO baser foundation <https://baserfoundation.org/>
 *
 * @copyright     Copyright (c) NPO baser foundation
 * @link          https://basercms.net baserCMS Project
 * @license       https://basercms.net/license/index.html MIT License
 */

namespace BcAddonMigrator\Utility;

/**
 * Class AddonConfigParser
 *
 * baserCMS 4 形式アドオンの config.php を「実行せずに」静的解析するユーティリティ。
 *
 * 従来 BcAddonMigrator は変換対象の config.php を include して設定値を読み取っていたが、
 * include はファイル内の PHP コードを実行するため、細工された config.php を含む ZIP を
 * 変換させることで任意コードが実行される脆弱性（JVN#21754394 / GHSA-jq83-g24q-wph5）が
 * あった。本クラスは include を用いず token_get_all によるトークン解析のみで、
 *
 *  - トップレベルが `return [...]` 形式かどうか（isReturnArray）
 *  - 変数代入形式（`$title = '...';` 等）の場合の各変数のリテラル値（vars）
 *
 * を取得する。ファイル内のコードは一切実行されないため、副作用（コード実行）は発生しない。
 */
class AddonConfigParser
{
    /**
     * config.php のソースコードを解析する
     *
     * @param string $code config.php の内容（`<?php ...` を含む PHP ソース）
     * @return array{isReturnArray: bool, vars: array} 解析結果
     */
    public static function parse(string $code): array
    {
        $sig = self::significantTokens($code);

        // 先頭の意味トークンが return なら `return [...]` 形式とみなす。
        // include しないため、配列値に関数呼び出しや副作用が含まれていても実行されない。
        if (!empty($sig) && $sig[0]['id'] === T_RETURN) {
            return ['isReturnArray' => true, 'vars' => []];
        }

        return ['isReturnArray' => false, 'vars' => self::extractAssignments($sig)];
    }

    /**
     * 空白・コメント・PHP タグを除いた意味のあるトークン列を返す
     *
     * @param string $code
     * @return array<int, array{id: int|null, text: string}>
     */
    protected static function significantTokens(string $code): array
    {
        $ignore = [
            T_WHITESPACE, T_COMMENT, T_DOC_COMMENT,
            T_OPEN_TAG, T_OPEN_TAG_WITH_ECHO, T_CLOSE_TAG, T_INLINE_HTML,
        ];
        $sig = [];
        foreach (token_get_all($code) as $token) {
            if (is_array($token)) {
                if (in_array($token[0], $ignore, true)) {
                    continue;
                }
                $sig[] = ['id' => $token[0], 'text' => $token[1]];
            } else {
                $sig[] = ['id' => null, 'text' => $token];
            }
        }
        return $sig;
    }

    /**
     * 変数代入形式（`$name = <リテラル>;`）から、リテラル値のみを抽出する
     *
     * 右辺が関数呼び出し・定数参照など「リテラルでない」場合、その変数は抽出せずスキップする
     * （呼び出し側でデフォルト値にフォールバックさせる）。
     *
     * @param array<int, array{id: int|null, text: string}> $sig
     * @return array<string, mixed>
     */
    protected static function extractAssignments(array $sig): array
    {
        $vars = [];
        $n = count($sig);
        $i = 0;
        while ($i < $n) {
            if (
                $sig[$i]['id'] === T_VARIABLE
                && isset($sig[$i + 1])
                && $sig[$i + 1]['id'] === null
                && $sig[$i + 1]['text'] === '='
            ) {
                $name = ltrim($sig[$i]['text'], '$');
                try {
                    [$value, $j] = self::parseValue($sig, $i + 2);
                    if (isset($sig[$j]) && $sig[$j]['id'] === null && $sig[$j]['text'] === ';') {
                        $vars[$name] = $value;
                        $i = $j + 1;
                        continue;
                    }
                } catch (\Throwable $e) {
                    // 非リテラル値 → この変数は抽出しない
                }
            }
            // 想定外・非リテラルは次の文の区切りまで読み飛ばす（安全側）
            $i = self::skipToSemicolon($sig, $i);
        }
        return $vars;
    }

    /**
     * 現在位置から次の `;` の直後まで進めたインデックスを返す
     *
     * @param array<int, array{id: int|null, text: string}> $sig
     * @param int $i
     * @return int
     */
    protected static function skipToSemicolon(array $sig, int $i): int
    {
        $n = count($sig);
        while ($i < $n) {
            if ($sig[$i]['id'] === null && $sig[$i]['text'] === ';') {
                return $i + 1;
            }
            $i++;
        }
        return $n;
    }

    /**
     * トークン列の $i 位置からリテラル値を 1 つ読み取り、[値, 次のインデックス] を返す
     *
     * 対応するのは文字列・整数・浮動小数点・true/false/null・配列リテラルのみ。
     * それ以外（関数呼び出し等）は例外を投げる。
     *
     * @param array<int, array{id: int|null, text: string}> $sig
     * @param int $i
     * @return array{0: mixed, 1: int}
     * @throws \RuntimeException リテラルでない場合
     */
    protected static function parseValue(array $sig, int $i): array
    {
        if (!isset($sig[$i])) {
            throw new \RuntimeException('unexpected end of tokens');
        }
        $tok = $sig[$i];

        // 符号付き数値
        if ($tok['id'] === null && ($tok['text'] === '-' || $tok['text'] === '+')) {
            $sign = $tok['text'] === '-' ? -1 : 1;
            $i++;
            if (!isset($sig[$i]) || !in_array($sig[$i]['id'], [T_LNUMBER, T_DNUMBER], true)) {
                throw new \RuntimeException('malformed number literal');
            }
            $num = $sig[$i]['id'] === T_LNUMBER ? (int)$sig[$i]['text'] : (float)$sig[$i]['text'];
            return [$sign * $num, $i + 1];
        }
        if ($tok['id'] === T_LNUMBER) {
            return [(int)$tok['text'], $i + 1];
        }
        if ($tok['id'] === T_DNUMBER) {
            return [(float)$tok['text'], $i + 1];
        }
        if ($tok['id'] === T_CONSTANT_ENCAPSED_STRING) {
            return [self::decodeString($tok['text']), $i + 1];
        }
        if ($tok['id'] === T_STRING) {
            switch (strtolower($tok['text'])) {
                case 'true':
                    return [true, $i + 1];
                case 'false':
                    return [false, $i + 1];
                case 'null':
                    return [null, $i + 1];
            }
            // 関数名・定数など
            throw new \RuntimeException('non-literal identifier: ' . $tok['text']);
        }
        // 配列 [ ... ]
        if ($tok['id'] === null && $tok['text'] === '[') {
            return self::parseArray($sig, $i + 1, ']');
        }
        // 配列 array( ... )
        if ($tok['id'] === T_ARRAY) {
            $i++;
            if (!isset($sig[$i]) || $sig[$i]['id'] !== null || $sig[$i]['text'] !== '(') {
                throw new \RuntimeException('malformed array() literal');
            }
            return self::parseArray($sig, $i + 1, ')');
        }

        throw new \RuntimeException('non-literal value');
    }

    /**
     * 配列リテラルの要素を読み取り、[配列, 次のインデックス] を返す
     *
     * @param array<int, array{id: int|null, text: string}> $sig
     * @param int $i 最初の要素（または閉じ括弧）の位置
     * @param string $close 閉じ括弧（']' または ')'）
     * @return array{0: array, 1: int}
     * @throws \RuntimeException リテラルでない要素を含む場合
     */
    protected static function parseArray(array $sig, int $i, string $close): array
    {
        $arr = [];
        $n = count($sig);

        // 空配列
        if (isset($sig[$i]) && $sig[$i]['id'] === null && $sig[$i]['text'] === $close) {
            return [$arr, $i + 1];
        }

        while ($i < $n) {
            [$first, $i] = self::parseValue($sig, $i);
            if (isset($sig[$i]) && $sig[$i]['id'] === T_DOUBLE_ARROW) {
                [$second, $i] = self::parseValue($sig, $i + 1);
                $arr[$first] = $second;
            } else {
                $arr[] = $first;
            }

            if (isset($sig[$i]) && $sig[$i]['id'] === null && $sig[$i]['text'] === ',') {
                $i++;
                // 末尾カンマを許容
                if (isset($sig[$i]) && $sig[$i]['id'] === null && $sig[$i]['text'] === $close) {
                    return [$arr, $i + 1];
                }
                continue;
            }
            if (isset($sig[$i]) && $sig[$i]['id'] === null && $sig[$i]['text'] === $close) {
                return [$arr, $i + 1];
            }
            throw new \RuntimeException('malformed array literal');
        }
        throw new \RuntimeException('unterminated array literal');
    }

    /**
     * 文字列リテラルトークン（引用符を含む）を実際の文字列へデコードする
     *
     * @param string $raw 例: "'foo'" や '"bar\n"'
     * @return string
     */
    protected static function decodeString(string $raw): string
    {
        if ($raw === '') {
            return '';
        }
        $quote = $raw[0];
        $inner = substr($raw, 1, -1);
        if ($quote === "'") {
            // シングルクォート: \\ と \' のみエスケープ解除
            return str_replace(['\\\\', "\\'"], ['\\', "'"], $inner);
        }
        // ダブルクォート: 標準的なエスケープシーケンスを解除
        return stripcslashes($inner);
    }
}
