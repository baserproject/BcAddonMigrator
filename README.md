# BcAddonMigrator plugin for baserCMS
Add-On Migrator for baserCMS   

## Installation
You can install this plugin into your baserCMS application using [composer](https://getcomposer.org).

The recommended way to install composer packages is:

```
composer require baserproject/bc-addon-migrator
```

## Documentation
See [baserCMS のアドオンを変換](https://baserproject.github.io/5/plugin/bc_addon_migrator)

## コマンドライン実行

コマンドラインからマイグレーションを実行できます。

`bin/cake bc_addon_migrator [--type=<type>] <zipファイルパス>`

### 引数
- `path`（必須）: baserCMS 4 のプラグインまたはテーマのZipファイルのパス。絶対パス・相対パスどちらでも指定可能です。

### オプション
- `--type`（省略可）: マイグレーションの種類を指定します。`plugin` または `theme` を指定できます（デフォルト: `plugin`）

### 実行例
```bash
# プラグインのマイグレーション
bin/cake bc_addon_migrator plugin.zip

# テーマのマイグレーション
bin/cake bc_addon_migrator --type=theme theme.zip
```

### 出力
- **ファイル**: 変換されたプラグインまたはテーマは、`tmp/<名前>.zip` に圧縮されて保存されます。保存先パスはコンソールに出力されます。
- **一時ディレクトリ**: マイグレーション完了後、作業用一時ディレクトリ（`tmp/addon_migrator/`）は自動的に削除されます。

## License
Lincensed under the MIT lincense since version 2.0
