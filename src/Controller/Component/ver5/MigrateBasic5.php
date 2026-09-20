<?php
/**
 * baserCMS :  Based Website Development Project <https://basercms.net>
 * Copyright (c) NPO baser foundation <https://baserfoundation.org/>
 *
 * @copyright     Copyright (c) NPO baser foundation
 * @link          https://basercms.net baserCMS Project
 * @since         5.0.7
 * @license       https://basercms.net/license/index.html MIT License
 */

namespace BcAddonMigrator\Controller\Component\ver5;

/**
 * Class MigrateBasic5
 */
class MigrateBasic5
{

	/**
     * 一時パス
     * @var string
     */
    public static $tmpPath;

    /**
     * コンストラクタ
     * @param string $tmpPath
     */
	public function __construct(string $tmpPath)
    {
        self::$tmpPath = $tmpPath;
    }

	public static function replaceCode(string $code, bool $is5): string
	{
		$code = preg_replace('/new Folder\(/', 'new \BaserCore\Utility\BcFolder(', $code);
		$code = preg_replace('/new File\(/', 'new \BaserCore\Utility\BcFile(', $code);
		$code = preg_replace('/new \\\Cake\\\Filesystem\\\Folder\(/', 'new \BaserCore\Utility\BcFolder(', $code);
		$code = preg_replace('/new \\\Cake\\\Filesystem\\\File\(/', 'new \BaserCore\Utility\BcFile(', $code);
		if($is5) return $code;

		// $this->pageTitle は4系のプロパティ。5系では BcBaserHelper の
		// setTitle() / getContentsTitle() を使う。
		// コントローラだけでなくテンプレートやレイアウトでも使われているため共通処理で変換する
		// （変換漏れがあると「BaserCore.pageTitleHelper could not be found」で500になる）
		// 参照側は getTitle() ではなく getContentsTitle()。getTitle() はパンくずと
		// サイトタイトルを連結するため、素のタイトルである $this->pageTitle とは別物になる。
		$code = preg_replace('/\\$this->pageTitle\\s*=\\s*(.+?);/', '$this->BcBaser->setTitle($1);', $code);
		$code = preg_replace('/\\$this->pageTitle\\b(?!\\s*=)/', '$this->BcBaser->getContentsTitle()', $code);

		// fullUrl() は4系のグローバル関数。5系では BcBaserHelper::getUrl($url, true) を使う
		$code = self::replaceFullUrl($code);

		// BcPageHelper::content() は5系に無い。固定ページの本文は $page->contents を直接出力する
		$code = preg_replace('/\\$this->BcPage->content\\(\\s*\\)\\s*;?/', 'echo $page->contents;', $code);

		// HtmlHelper::url() は CakePHP 5 で廃止。現在のURLを表す用途なので
		// BcBaserHelper::getUrl() の引数 null（＝現在のURL）に置き換える
		$code = str_replace('$this->BcBaser->getUrl($this->Html->url(), true)', '$this->BcBaser->getUrl(null, true)', $code);
		$code = str_replace('$this->Html->url()', '$this->BcBaser->getUrl(null)', $code);

		$code = preg_replace('/([^\\\])BcUtil::/', "$1\BaserCore\Utility\BcUtil::", $code);
		$code = preg_replace('/new BcZip\(/', 'new \BaserCore\Utility\BcZip(', $code);
		$code = preg_replace('/App::uses\(.+?;\n/', "\n", $code);
		$code = preg_replace('/Hash::/', '\Cake\Utility\Hash::', $code);
		$code = preg_replace('/Configure::/', '\Cake\Core\Configure::', $code);
		$code = preg_replace('/Inflector::/', '\Cake\Utility\Inflector::', $code);
		$code = preg_replace('/ClassRegistry::init\(/', '\Cake\ORM\TableRegistry::getTableLocator()->get(', $code);
		$code = preg_replace('/getTableLocator\(\)->get\(\'Blog.BlogPost\'\)/', "getTableLocator()->get('BcBlog.BlogPosts')", $code);
		$code = preg_replace('/getTableLocator\(\)->get\(\'Blog.BlogContent\'\)/', "getTableLocator()->get('BcBlog.BlogContents')", $code);
		$code = preg_replace('/getTableLocator\(\)->get\(\'Blog.BlogComment\'\)/', "getTableLocator()->get('BcBlog.BlogComments')", $code);
		$code = preg_replace('/getTableLocator\(\)->get\(\'Blog.BlogCategory\'\)/', "getTableLocator()->get('BcBlog.BlogCategories')", $code);
		$code = preg_replace('/getTableLocator\(\)->get\(\'Blog.BlogTag\'\)/', "getTableLocator()->get('CuBlog.BlogTags')", $code);
		$code = preg_replace('/\sgetVersion\(\)/', '\BaserCore\Utility\BcUtil::getVersion()', $code);
		$code = preg_replace('/\$this->request/', '$this->getRequest()', $code);
        $code = preg_replace('/\$this->getRequest\(\)->here/', "\$this->getRequest()->getPath()", $code);
		$code = preg_replace('/\$this->getRequest\(\)->params/', "\$this->getRequest()->getAttribute('params')", $code);

		// 4系の params['Content'] 配列は5系に無く、リクエスト属性 currentContent の
		// エンティティに変わっている
		$code = preg_replace("/getRequest\\(\\)->getAttribute\\('params'\\)\\['Content'\\]\\['(\\w+)'\\]/", "getRequest()->getAttribute('currentContent')->$1", $code);

		// 4系の View::$action は5系に無い。未定義プロパティがヘルパー名として解決され
		// 「<Plugin>.actionHelper could not be found」になる。
		// あわせて5系ではアクション名から admin_ プレフィックスが外れている。
		$code = preg_replace('/\$this->action\s*==\s*\'admin_(\w+)\'/', '$this->getRequest()->getParam(\'action\') === \'${1}\'', $code);
		$code = preg_replace('/\$this->action\s*==\s*\'(\w+)\'/', '$this->getRequest()->getParam(\'action\') === \'${1}\'', $code);
		// 2階層
		$code = preg_replace('/\$this->getRequest\(\)->data\[\'([^\]]+?)\']\[\'([^\]]+?)\'\](?!(\s*=))/', "\$this->getRequest()->getData('$1.$2')", $code);
		$code = preg_replace('/\$this->getRequest\(\)->data\[\'([^\]]+?)\']\[\'([^\]]+?)\'\]\s*=\s(.+?);/', "\$this->setRequest(\$this->getRequest()->withData('$1.$2', $3));", $code);
		// 1階層
		$code = preg_replace('/\$this->getRequest\(\)->data\[\'([^\]]+?)\'](?!(\s*=|\[\'))/', "\$this->getRequest()->getData('$1')", $code);
		$code = preg_replace('/\$this->getRequest\(\)->data\[\'([^\]]+?)\']\s*=\s(.+?);/', "\$this->setRequest(\$this->getRequest()->withData('$1', $2));", $code);
		// 0階層
		$code = preg_replace('/\$this->getRequest\(\)->data(?!(\s*=|\[\'))/', "\$this->getRequest()->getData()", $code);
		$code = preg_replace('/\$this->getRequest\(\)->data\s*=\s(.+?);/', "\$this->setRequest(\$this->getRequest()->withParsedBody($1));", $code);
		return $code;
	}

	/**
	 * ネームスペースを追加する
	 * @param string $plugin
	 * @param string $path
	 * @param string $code
	 * @return string
	 */
	public static function addNameSpace(string $plugin, string $path, string $layerPath, string $code)
	{
		if (preg_match('/namespace/', $code)) return $code;

		$path = dirname($path);
		$path = str_replace(TMP_ADDON_MIGRATOR . $plugin . DS . 'src' . DS . $layerPath, '', $path);
		$nameSpace = $plugin . "\\" . str_replace(DS, "\\", $layerPath);
		if ($path) {
			$nameSpace .= "\\" . preg_replace('/^\//', '', $path);
		}
		$codeArray = explode("\n", $code);
		array_splice($codeArray, 1, 0, 'namespace ' . $nameSpace . ';');
		return implode("\n", $codeArray);
	}


	/**
	 * fullUrl() を $this->BcBaser->getUrl($url, true) に置き換える
	 *
	 * fullUrl() は4系のグローバル関数で5系には存在しない（呼ぶと
	 * 「Call to undefined function fullUrl()」で500になる）。
	 * 引数に $this->Html->url() のような括弧を含む式が来るため、
	 * 正規表現ではなく括弧の対応を数えて引数の範囲を特定する。
	 *
	 * @param string $code
	 * @return string
	 */
	public static function replaceFullUrl(string $code): string
	{
		$offset = 0;
		while(($pos = strpos($code, 'fullUrl(', $offset)) !== false) {
			// メソッド呼び出し（->fullUrl( など）や他の識別子の一部は対象外
			$prev = $pos > 0? $code[$pos - 1] : ' ';
			if (preg_match('/[A-Za-z0-9_>$]/', $prev)) {
				$offset = $pos + 8;
				continue;
			}
			// 引数の閉じ括弧を探す
			$depth = 0;
			$end = null;
			for($i = $pos + 7; $i < strlen($code); $i++) {
				if ($code[$i] === '(') {
					$depth++;
				} elseif ($code[$i] === ')') {
					$depth--;
					if ($depth === 0) { $end = $i; break; }
				}
			}
			if ($end === null) break;

			$args = substr($code, $pos + 8, $end - $pos - 8);
			$replacement = '$this->BcBaser->getUrl(' . $args . ', true)';
			$code = substr($code, 0, $pos) . $replacement . substr($code, $end + 1);
			$offset = $pos + strlen($replacement);
		}
		return $code;
	}

}
