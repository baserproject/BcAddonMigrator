<?php
/**
 * baserCMS :  Based Website Development Project <https://basercms.net>
 * Copyright (c) NPO baser foundation <https://baserfoundation.org/>
 *
 * @copyright     Copyright (c) NPO baser foundation
 * @link          https://basercms.net baserCMS Project
 * @since         5.0.0
 * @license       https://basercms.net/license/index.html MIT License
 */

namespace BcAddonMigrator\Command;

use BaserCore\Utility\BcFolder;
use BaserCore\Utility\BcZip;
use BcAddonMigrator\Controller\Component\BcAddonMigrator5Component;
use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Controller\ComponentRegistry;
use Cake\Controller\Controller;
use Cake\Event\Event;
use Cake\Http\ServerRequest;
use Laminas\Diactoros\UploadedFile;

/**
 * BcAddonMigratorCommand
 */
class BcAddonMigratorCommand extends Command
{

    /**
     * buildOptionParser
     *
     * @param ConsoleOptionParser $parser
     * @return ConsoleOptionParser
     */
    public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = parent::buildOptionParser($parser);
        $parser->addArgument('path', [
            'help' => 'プラグインまたはテーマのZipファイルのパス',
            'required' => true
        ]);
        $parser->addOption('type', [
            'short' => 't',
            'help' => 'マイグレーションのタイプ (plugin または theme)',
            'default' => 'plugin',
            'choices' => ['plugin', 'theme']
        ]);
        return $parser;
    }

    /**
     * execute
     *
     * @param Arguments $args
     * @param ConsoleIo $io
     * @return int|void|null
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $path = $args->getArgument('path');
        $type = $args->getOption('type');

        if (!file_exists($path)) {
            $io->error('指定されたZipファイルが存在しません: ' . $path);
            return static::CODE_ERROR;
        }

        $tmpPath = TMP_ADDON_MIGRATOR;

        // 元ファイルを残すため、一時ディレクトリにコピーしてからUploadedFileを作成
        $io->out('Zipファイルを準備しています...');

        // 一時ディレクトリを準備
        $tempDir = TMP . 'bc_addon_migrator_upload' . DS;
        $folder = new BcFolder($tempDir);
        $folder->delete();
        $folder->create(0777);

        // 元ファイルをコピー
        $copiedPath = $tempDir . basename($path);
        if (!copy($path, $copiedPath)) {
            $io->error('Zipファイルのコピーに失敗しました。');
            return static::CODE_ERROR;
        }

        // コピーしたファイルでUploadedFileオブジェクトを作成
        $uploadedFile = new UploadedFile(
            $copiedPath,
            filesize($copiedPath),
            UPLOAD_ERR_OK,
            basename($copiedPath),
            'application/zip'
        );

        // ダミーコントローラーのセットアップ
        $request = new ServerRequest();
        $controller = new Controller($request);

        $registry = new ComponentRegistry($controller);
        $migrator = new BcAddonMigrator5Component($registry);

        // マイグレーション実行
        $io->out('マイグレーションを開始します...');

        try {
            if ($type === 'plugin') {
                $result = $migrator->migratePlugin($uploadedFile);
            } else {
                $result = $migrator->migrateTheme($uploadedFile);
            }

            if ($result) {
                $io->success('マイグレーションが完了しました: ' . $result);

                // バージョン番号を付けたZIP圧縮（解凍時のディレクトリ名は元のまま）
                $io->out('変換ファイルを圧縮しています...');
                $version = str_replace(' ', '_', \BaserCore\Utility\BcUtil::getVersion());
                $distPath = TMP . $result . '_' . $version . '.zip';

                // ZIPを作成（解凍時のフォルダ名はバージョンサフィックスなし）
                $sourceDir = $tmpPath . $result;

                // ZIP内でのルートディレクトリ名を指定してZIP化
                // 親ディレクトリ(tmpPath)からZIPを作成し、結果ディレクトリのみを含める
                $za = new \ZipArchive();
                $za->open($distPath, \ZIPARCHIVE::CREATE);

                // ディレクトリの内容を再帰的に追加（ルートディレクトリ名を含む）
                $this->addDirectoryToZip($za, $sourceDir, $result . DS);

                $za->close();

                $io->success('変換ファイルを圧縮しました: ' . $distPath);

                // 作業フォルダの削除
                $folder = new BcFolder($tmpPath);
                $folder->delete();

                return static::CODE_SUCCESS;
            } else {
                $io->error('マイグレーションに失敗しました。ログを確認してください。');
                return static::CODE_ERROR;
            }
        } catch (\Throwable $e) {
            $io->error('マイグレーション中にエラーが発生しました: ' . $e->getMessage());
            return static::CODE_ERROR;
        }
    }

    /**
     * ディレクトリの内容を再帰的にZIPに追加する
     *
     * @param \ZipArchive $za
     * @param string $path
     * @param string $parentPath
     * @return void
     */
    private function addDirectoryToZip($za, $path, $parentPath = '')
    {
        $dh = opendir($path);
        while(($entry = readdir($dh)) !== false) {
            if ($entry == '.' || $entry == '..') {
                continue;
            }
            $localPath = $parentPath . $entry;
            $fullpath = $path . DS . $entry;
            if (is_file($fullpath)) {
                $za->addFile($fullpath, $localPath);
            } else if (is_dir($fullpath)) {
                $za->addEmptyDir($localPath);
                $this->addDirectoryToZip($za, $fullpath, $localPath . DS);
            }
        }
        closedir($dh);
    }
}
