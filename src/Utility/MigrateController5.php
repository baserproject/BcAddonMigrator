<?php

namespace BcAddonMigrator\Utility;

use Cake\Log\LogTrait;
use Cake\Filesystem\File;
use Cake\Filesystem\Folder;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use Psr\Log\LogLevel;

class MigrateController5
{
	
	use LogTrait;
	
	public function migrate($plugin, $prefix, $path)
	{
		$code = file_get_contents($path);
		$code = self::addNameSpace($plugin, $path, $code);
		$code = self::replaceBeforeFilter($code);
		$code = self::replaceMessage($code);
		$code = self::replaceComponents($code);
		$code = self::replaceEtc($code);
		if (!$prefix) $code = self::replaceAdmin($plugin, $path, $code);
		file_put_contents($path, $code);
		$this->log('コントローラーファイル：' . basename($path) . 'を マイグレーションしました。', LogLevel::INFO);
	}
	
	/**
	 * その他の置き換え
	 * @param $code
	 * @return array|string|string[]|null
	 */
	public static function replaceEtc($code)
	{
		$code = preg_replace('/extends\s+AppController/', 'extends \BaserCore\Controller\BcFrontAppController', $code);
		$code = preg_replace('/new Folder\(/', 'new \Cake\Filesystem\Folder(', $code);
		$code = preg_replace('/new File\(/', 'new \Cake\Filesystem\File(', $code);
		$code = preg_replace('/new BcZip\(/', 'new \BaserCore\Utility\BcZip(', $code);
		$code = preg_replace('/App::uses\(.+?;\n/', '', $code);
		$code = preg_replace('/\$this->request->data\)/', '$this->request->getData())', $code);
		$code = preg_replace('/Configure::/', '\Cake\Core\Configure::', $code);
		$code = preg_replace('/Inflector::/', '\Cake\Utility\Inflector::', $code);
		$code = preg_replace('/ClassRegistry::init\(/', '\Cake\ORM\TableRegistry::getTableLocator()->get(', $code);
		return $code;
	}
	
	/**
	 * ネームスペースを追加する
	 * @param string $plugin
	 * @param string $path
	 * @param string $code
	 * @return string
	 */
	public static function addNameSpace(string $plugin, string $path, string $code)
	{
		if (preg_match('/namespace/', $code)) return $code;
		
		$path = dirname($path);
		$path = str_replace(BASER_PLUGINS . $plugin . DS . 'src' . DS . 'Controller', '', $path);
		$nameSpace = $plugin . "\\" . 'Controller';
		if ($path) {
			$nameSpace .= "\\" . preg_replace('/^\//', '', $path);
		}
		$codeArray = explode("\n", $code);
		array_splice($codeArray, 1, 0, 'namespace ' . $nameSpace . ';');
		return implode("\n", $codeArray);
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
		$code = preg_replace('/extends Component/', 'extends \Cake\Controller\Component', $code);
		return $code;
	}
	
	public static function replaceAdmin(string $plugin, string $path, string $code)
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
