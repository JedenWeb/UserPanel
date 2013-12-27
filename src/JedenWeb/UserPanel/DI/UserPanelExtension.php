<?php

namespace JedenWeb\UserPanel\DI;

use Nette;
use Nette\DI\Compiler;
use Nette\DI\Configurator;


/** @author Filip Procházka */
if (!class_exists('Nette\DI\CompilerExtension')) {
	class_alias('Nette\Config\CompilerExtension', 'Nette\DI\CompilerExtension');
	class_alias('Nette\Config\Compiler', 'Nette\DI\Compiler');
	class_alias('Nette\Config\Helpers', 'Nette\DI\Config\Helpers');
}

if (isset(Nette\Loaders\NetteLoader::getInstance()->renamed['Nette\Configurator']) || !class_exists('Nette\Configurator')) {
	unset(Nette\Loaders\NetteLoader::getInstance()->renamed['Nette\Configurator']);
	class_alias('Nette\Config\Configurator', 'Nette\Configurator');
}

/**
 * @author Pavel Jurásek <jurasekpavel@ctyrimedia.cz>
 */
class UserPanelExtension extends Nette\DI\CompilerExtension
{

	/** @var array */
	private $defaults = array(
		'column' => 'username',
		'credentials' => array(),
	);
	

	public function loadConfiguration()
	{
		$config = $this->getConfig($this->defaults);
		$container = $this->getContainerBuilder();
		
		if ($container->parameters['debugMode']) {
			$panel = $container->addDefinition($this->prefix('panel'))
				->setClass('JedenWeb\UserPanel\UserPanel')
				->addSetup('Nette\Diagnostics\Debugger::getBar()->addPanel(?)', array('@self'))
				->addSetup('setColumnName', array($config['column']));
			
			foreach ($config['credentials'] as $username => $roles) {
				$panel->addSetup('addCredentials', array($username, $roles));
			}
			
			$container->getDefinition('nette.application')
				->addSetup('?->onCreatePresenter[] = ?', array('@self', array($this->prefix('@panel'), 'register')));
		}
	}
	
	
	/**
	 * @author David Grudl
	 * @param array $config
	 * @param array $expected
	 * @param type $name
	 * @throws Nette\InvalidStateException
	 */
	private function validate(array $config, array $expected, $name)
	{
		if ($extra = array_diff_key($config, $expected)) {
			$extra = implode(", $name.", array_keys($extra));
			throw new Nette\InvalidStateException("Unknown option $name.$extra.");
		}
	}
	

	/**
	 * @param \Nette\Config\Configurator $config
	 * @param string $extensionName
	 */
	public static function register(Configurator $config, $extensionName = 'userPanel')
	{
		$config->onCompile[] = function (Configurator $config, Compiler $compiler) use ($extensionName) {
			$compiler->addExtension($extensionName, new ImagesExtension());
		};
	}

}
