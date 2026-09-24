<?php
/**
 * baserCMS :  Based Website Development Project <https://basercms.net>
 * Copyright (c) NPO baser foundation <https://baserfoundation.org/>
 *
 * @copyright     Copyright (c) NPO baser foundation
 * @link          https://basercms.net baserCMS Project
 * @license       https://basercms.net/license/index.html MIT License
 */

namespace BcAddonMigrator\Test\TestCase\Utility;

use BcAddonMigrator\Utility\AddonConfigParser;
use PHPUnit\Framework\TestCase;

// BcAddonMigrator は composer psr-4 未登録のため、依存なく単体実行できるよう明示的に読み込む。
require_once dirname(__DIR__, 3) . '/src/Utility/AddonConfigParser.php';

/**
 * AddonConfigParser のテスト
 *
 * JVN#21754394 / GHSA-jq83-g24q-wph5 の回帰テストを含む。
 */
class AddonConfigParserTest extends TestCase
{
    /**
     * `return [...]` 形式は、値に関数呼び出し（__d 等）を含んでいても return 配列と判定される
     */
    public function testReturnArrayFormWithFunctionCallIsRecognized(): void
    {
        $code = "<?php\nreturn ['type'=>'Plugin','title'=>__d('baser_core','X'),'adminLink'=>['plugin'=>'p']];";
        $result = AddonConfigParser::parse($code);
        $this->assertTrue($result['isReturnArray']);
        $this->assertSame([], $result['vars']);
    }

    /**
     * 変数代入形式ではスカラ値が抽出され、isReturnArray は false になる
     */
    public function testAssignmentFormExtractsScalars(): void
    {
        $code = "<?php\n\$title='Foo';\n\$description='Bar';\n\$url='https://example.com';";
        $result = AddonConfigParser::parse($code);
        $this->assertFalse($result['isReturnArray']);
        $this->assertSame('Foo', $result['vars']['title']);
        $this->assertSame('Bar', $result['vars']['description']);
        $this->assertSame('https://example.com', $result['vars']['url']);
    }

    /**
     * セキュリティ回帰: return 文の前に置かれた副作用文は parse 中に実行されない
     */
    public function testSideEffectStatementIsNotExecutedDuringParse(): void
    {
        $marker = sys_get_temp_dir() . '/adp_' . uniqid('', true) . '.txt';
        @unlink($marker);
        $code = "<?php\n@file_put_contents('" . $marker . "','PWNED');\nreturn ['type'=>'Plugin','title'=>'x'];";

        AddonConfigParser::parse($code);

        $this->assertFileDoesNotExist($marker, 'config.php 内の副作用文が実行されてはならない');
        @unlink($marker);
    }

    /**
     * セキュリティ回帰: 配列値に仕込まれた副作用も parse 中に実行されない
     */
    public function testSideEffectInArrayValueIsNotExecuted(): void
    {
        $marker = sys_get_temp_dir() . '/adp_' . uniqid('', true) . '.txt';
        @unlink($marker);
        $code = "<?php\nreturn ['x'=>@file_put_contents('" . $marker . "','PWNED')];";

        AddonConfigParser::parse($code);

        $this->assertFileDoesNotExist($marker, '配列値内の副作用が実行されてはならない');
        @unlink($marker);
    }

    /**
     * 変数代入形式の配列リテラル値（adminLink 等）も抽出される
     */
    public function testArrayLiteralVariableExtracted(): void
    {
        $code = "<?php\n\$title='T';\n\$adminLink=['plugin'=>'pp','controller'=>'cc','action'=>'index'];";
        $result = AddonConfigParser::parse($code);
        $this->assertSame('pp', $result['vars']['adminLink']['plugin']);
        $this->assertSame('cc', $result['vars']['adminLink']['controller']);
        $this->assertSame('index', $result['vars']['adminLink']['action']);
    }

    /**
     * 右辺が非リテラル（関数呼び出し）の代入はスキップされ、他のリテラル代入は抽出される
     */
    public function testNonLiteralAssignmentIsSkipped(): void
    {
        $code = "<?php\n\$title=strtoupper('x');\n\$description='ok';";
        $result = AddonConfigParser::parse($code);
        $this->assertArrayNotHasKey('title', $result['vars']);
        $this->assertSame('ok', $result['vars']['description']);
    }
}
