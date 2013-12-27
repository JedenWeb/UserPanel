<?php

/**
 * UserPanel for Nette 2.1
 * 
 * @license MIT
 */

namespace JedenWeb\UserPanel;

use Nette;
use Nette\Application\UI\Form;

/**
 * @author Pavel Jurásek <jurasekpavel@ctyrimedia.cz>
 * @author Mikuláš Dítě
 */
class UserPanel extends Nette\Application\UI\Control implements Nette\Diagnostics\IBarPanel
{
	
	const GUEST_KEY = '__guest';

	/** @var \Nette\Security\User */
	private $user;
	
	/** @var string */
	private $columnName;

	/** @var array key => [username, roles] */
	private $credentials = array();


	/**
	 * @param Nette\Security\User $user
	 */
	public function __construct(Nette\Security\User $user)
	{
		$this->user = $user;
		
		parent::__construct();
	}
	
	
	/**
	 * Register panel to Debugger
	 */
	public function register(\Nette\Application\Application $application, \Nette\Application\IPresenter $presenter)
	{
		$presenter->addComponent($this, '_userPanel');
	}
	
	
	/********************* getters *********************/


	/**
	 * Get username from IIdentity::$data from column set via setColumnName()
	 * @return string|NULL
	 */
	public function getUsername()
	{
		$data = $this->getData();
		return isset($data[$this->columnName]) ? $data[$this->columnName] : NULL;
	}


	/**
	 * Get user's identity data
	 * @return array
	 */
	private function getData()
	{
		if ($this->user->identity instanceof Nette\Security\IIdentity) {
			return $this->user->identity->getData();
		}

		return array();
	}
	
	
	/********************* setters *********************/
	
	
	/**
	 * Set column name to be used as username in IIdentity::$data array
	 * @param string $column
	 * @return UserPanel Provides fluent interface
	 */
	public function setColumnName($column)
	{
		$reserved = array('roles');
		if (in_array($column, $reserved)) {
			throw new Nette\InvalidArgumentException("Column name '$column' is reserved keyword. Choose another one.");
		}
		
		$this->columnName = $column;
		return $this;
	}
	

	/**
	 * Add user credentials to User Panel
	 * @param string $username
	 * @param array $roles
	 * @return UserPanel Provides fluent interface
	 */
	public function addCredentials($username, array $roles = array())
	{
		if ($username === self::GUEST_KEY) {
			throw new \Nette\InvalidArgumentException("Default user '$username' cannot be redeclared.");
		}
		
		$this->credentials[\Nette\Utils\Strings::webalize($username)] = array(
			$this->columnName => $username,
			'roles' => $roles,
		);
		return $this;
	}
	
	
	/********************* Login form *********************/


	/**
	 * @return array
	 */
	private function getCredentialsRadioData()
	{	
		$list = array(
			self::GUEST_KEY => 'Guest (logout)',
		);
		
		foreach ($this->credentials as $key => $data) {
			$list[$key] = ucfirst($data[$this->columnName]) . ' [' . implode(', ', $data['roles']) . ']';
		}
		
		return $list;
	}


	/**
	 * Sign in form component factory.
	 * @return Form
	 */
	public function createComponentLogin($name)
	{
		$form = new Form($this, $name);
		$form->addProtection();
		
		$form->addRadioList('user', NULL, $this->getCredentialsRadioData())
			->setAttribute('class', 'onClickSubmit');

		$form->addSubmit('submit', 'Login');

		$form->onSuccess[] = callback($this, 'onLoginSubmitted');
		return $form;
	}


	/**
	 * @param Form $form
	 */
	public function onLoginSubmitted(Form $form)
	{
		$username = $form->getValues()->user;
		
		if ($username === self::GUEST_KEY) {
			
			$this->user->logout(TRUE);
			
		} else {
			
			$this->user->login(new Nette\Security\Identity($username, $this->credentials[$username]['roles'], array(
				$this->columnName => $this->credentials[$username][$this->columnName],
			)));

		}

		$this->redirect('this');
	}
	
	
	/********************* Nette\Diagnostics\IBarPanel *********************/


	/**
	 * Render panel tab in Debugger
	 * @return string
	 */
	public function getTab()
	{
		$data = $this->getData();
		return '<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAnpJREFUeNqEU19IU1EY/927e52bWbaMQLbJwmgP0zIpffDFUClsyF56WJBQkv1RyJeo2IMPEghRQeAIoscegpBqTy6y3CDwrdzDwjCVkdqmzT+7u//O1jm3knkV/MF3z3e+8zu/7zv3O4crFotgaHC7jfHrwgKuBYPtVqt1BBx3SlNV5HK5KSmXu/N6fPxTKY+BMwvUNzY22cvFz6TIi0TXoWkaFEWBrkra+rrUtJLJTJcKCDCBZrqvyBaRCTMBnRCwKhRZFlVFuUspl0r5OwRUKXu+opxgsP8qfE4Bmk7wZV7Bg5FRqIR0m/m8OfA7K9n6bt1GvbeWlq2CKxCcPnEM1wf6sZknFXsKDF+c+dHgVKBmf4JoqmHMb/Va8OTK4vSeAhThpW9vwdsPociJ1ATD/zU7bqyZyVtdKMWHIXH0SJ3/RrWn05hn5t5jeeZN+OyQdtPMFbA77i1/f9dE7cy/+RS10G7EbRX4fL42OvQGAoFgT6uM2uPnjHhq9iNeTABjY2Mv6fR5IpGY2Cbg9XqPUr/PZrMNOJ1Oq65pfCQSwcPwK1TtE9F7OYCurgsQRbGQSqWUfD7/lPKfJZPJWc7j8ZzkeX7S5XLZHA6HIEkSqBCam5uxYqnDwf02WDeTiMVikGUZdrsdq6urOhWSCSGdFhoIud3ulrKyMiGbzRrXVqX9j8fj8Pu7UXO4EiPDIZYdNDN7F6DvhKf7+HQ6bRGoaju970bm/2CZmCXn0nAcyBn+xsbG1joTooJsbxv71LDNhUJh299lpPnFNaxt/hVjlZWCPTIar+YEQXhEzzxobk9HRyeWrC2oqhRRnplENBrd0UKa5PEfAQYAH6s95RSa3ooAAAAASUVORK5CYII=">' .
			($this->user->isLoggedIn() ? 'Logged as <span style="font-style: italic; margin: 0; padding: 0;">' . $this->getUsername() . '</span>' : 'Guest');
	}


	/**
	 * Render panel
	 * @return string
	 */
	public function getPanel()
	{	
		$default = $this->user->isLoggedIn() ? $this->user->getId() : self::GUEST_KEY;
		
		if (array_key_exists($default, $this['login']['user']->getItems())) {
			$this['login']['user']->setDefaultValue($default);
		}
		
		ob_start();
		$template = parent::getTemplate();
		$template->setFile(__DIR__ . '/panel.latte');

		$template->data = $this->getData();
		$template->username = $this->getUsername();

		$template->render();

		return ob_get_clean();
	}	

}
