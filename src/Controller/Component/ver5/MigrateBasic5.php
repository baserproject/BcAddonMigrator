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
        $code = preg_replace('/\$this->getRequest\(\)->here/', "\$this->getRequest()->getAttribute('here')", $code);
		$code = preg_replace('/\$this->getRequest\(\)->params/', "\$this->getRequest()->getAttribute('params')", $code);
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

}
