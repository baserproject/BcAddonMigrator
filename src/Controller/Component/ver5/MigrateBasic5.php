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
	
	public static function replaceCode(string $code): string
	{
		$code = preg_replace('/new Folder\(/', 'new \Cake\Filesystem\Folder(', $code);
		$code = preg_replace('/new File\(/', 'new \Cake\Filesystem\File(', $code);
		$code = preg_replace('/new BcZip\(/', 'new \BaserCore\Utility\BcZip(', $code);
		$code = preg_replace('/App::uses\(.+?;\n/', '', $code);
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
		$path = str_replace(BASER_PLUGINS . $plugin . DS . 'src' . DS . $layerPath, '', $path);
		$nameSpace = $plugin . "\\" . str_replace(DS, "\\", $layerPath);
		if ($path) {
			$nameSpace .= "\\" . preg_replace('/^\//', '', $path);
		}
		$codeArray = explode("\n", $code);
		array_splice($codeArray, 1, 0, 'namespace ' . $nameSpace . ';');
		return implode("\n", $codeArray);
	}
	
}
