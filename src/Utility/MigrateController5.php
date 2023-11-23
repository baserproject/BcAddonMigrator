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

namespace BcAddonMigrator\Utility;

use Cake\Log\LogTrait;
use Cake\Filesystem\Folder;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use Psr\Log\LogLevel;

/**
 * Class MigrateController5
 */
class MigrateController5
{
	
	/**
	 * Trait
	 */
	use LogTrait;
	
	/**
	 * マイグレーション
	 * @param string $plugin
	 * @param string $prefix
	 * @param string $path
	 * @return void
	 */
	public function migrate(string $plugin, string $path): void
	{
		$code = file_get_contents($path);
		$code = MigrateBasic5::addNameSpace($plugin, $path, 'Controller', $code);
		$code = self::replaceBeforeFilter($code);
		$code = self::replaceMessage($code);
		$code = self::replaceComponents($code);
		$code = self::replaceEtc($code);
		$code = MigrateBasic5::replaceCode($code);
		$code = self::separateAdmin($plugin, $path, $code);
		file_put_contents($path, $code);
		$this->log('コントローラー：' . $path . ' をマイグレーションしました。', LogLevel::INFO);
	}
	
	/**
	 * その他の置き換え
	 * @param $code
	 * @return array|string|string[]|null
	 */
	public static function replaceEtc($code)
	{
		$code = preg_replace('/extends\s+AppController/', 'extends \BaserCore\Controller\BcFrontAppController', $code);
		$code = preg_replace('/\$this->request->data\)/', '$this->request->getData())', $code);
		$code = preg_replace('/\$this->pageTitle = (.+?);/', '$this->setTitle($1);', $code);
		return $code;
	}
	
	/**
	 * beforeFilterを置き換える
	 * @param string $code
	 * @return array|string|string[]|null
	 */
	public static function replaceBeforeFilter(string $code)
	{
		$code = preg_replace('/function beforeFilter\(\)/', 'function beforeFilter(\Cake\Event\EventInterface $event)', $code);
		return preg_replace('/parent::beforeFilter\(\)/', 'parent::beforeFilter($event)', $code);
	}
	
	/**
	 * setMessageを置き換える
	 * @param string $code
	 * @return array|string|string[]|null
	 */
	public function replaceMessage(string $code)
	{
		$code = preg_replace('/\$this->setMessage\((.+?), true\)/', '$this->BcMessage->setWarning($1)', $code);
		$code = preg_replace('/\$this->setMessage\(([^,]+?)\)/', '$this->BcMessage->setInfo($1)', $code);
		return $code;
	}
	
	/**
	 * Componentsを置き換える
	 * @param string $code
	 * @return array|string|string[]
	 */
	public function replaceComponents(string $code)
	{
		$code = str_replace('$this->Components->load(', '$this->loadComponent(', $code);
		return $code;
	}
	
	/**
	 * 管理画面用のメソッドを分離する
	 * @param string $plugin
	 * @param string $path
	 * @param string $code
	 * @return string
	 */
	public static function separateAdmin(string $plugin, string $path, string $code): string
	{
		$adminPath = BASER_PLUGINS . $plugin . DS . 'src' . DS . 'Controller' . DS . 'Admin' . DS;
		if (!is_dir($adminPath)) {
			(new Folder())->create($adminPath);
		}
		
		$parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7);
		try {
			$namespaces = $parser->parse($code);
			$adminNamespaces = $parser->parse($code);
		} catch (\Exception $e) {
			echo 'Parse Error: ', $e->getMessage();
			return $code;
		}
		foreach($adminNamespaces as $i => $namespace) {
			$namespace->name = new Name($namespace->name . '\\Admin');
			foreach($namespace->stmts as $j => $class) {
				if (!($class instanceof \PhpParser\Node\Stmt\Class_)) continue;
				$class->extends = new Name\FullyQualified('BaserCore\Controller\Admin\BcAdminAppController');
				foreach($class->stmts as $k => $method) {
					if ($method instanceof ClassMethod) {
						if (preg_match('/^admin_/', $method->name)) {
							$method->name = preg_replace('/^admin_/', '', $method->name);
							unset($namespaces[$i]->stmts[$j]->stmts[$k]);
						} else {
							unset($class->stmts[$k]);
						}
					} else {
						unset($class->stmts[$k]);
					}
				}
			}
		}
		
		$prettyPrinter = new Standard();
		$code = $prettyPrinter->prettyPrintFile($namespaces);
		$adminCode = $prettyPrinter->prettyPrintFile($adminNamespaces);
		file_put_contents($adminPath . basename($path), $adminCode);
		return $code;
	}
	
}
