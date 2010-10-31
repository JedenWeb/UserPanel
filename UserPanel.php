<?php
/**
 * UserPanel for Nette 2.0
 *
 * @author Mikuláš Dítě
 * @license MIT
 */

namespace Panel;
use Nette\Debug;
use Nette\IDebugPanel;
use Nette\Object;
use Nette\Templates\FileTemplate;
use Nette\Templates\LatteFilter;


class UserPanel extends \Nette\Application\Control implements IDebugPanel
{

	/** @var \Nette\Web\User */
	private $user;


	
	public function __construct()
	{
		parent::__construct(\Nette\Environment::getApplication()->presenter, $this->reflection->shortName);
		$this->user = \Nette\Environment::getUser();
	}



	/**
	 * Renders HTML code for custom tab
	 * IDebugPanel
	 * @return void
	 */
	public function getTab()
	{
		return '<img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAYAAAAf8/9hAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAnpJREFUeNqEU19IU1EY/927e52bWbaMQLbJwmgP0zIpffDFUClsyF56WJBQkv1RyJeo2IMPEghRQeAIoscegpBqTy6y3CDwrdzDwjCVkdqmzT+7u//O1jm3knkV/MF3z3e+8zu/7zv3O4crFotgaHC7jfHrwgKuBYPtVqt1BBx3SlNV5HK5KSmXu/N6fPxTKY+BMwvUNzY22cvFz6TIi0TXoWkaFEWBrkra+rrUtJLJTJcKCDCBZrqvyBaRCTMBnRCwKhRZFlVFuUspl0r5OwRUKXu+opxgsP8qfE4Bmk7wZV7Bg5FRqIR0m/m8OfA7K9n6bt1GvbeWlq2CKxCcPnEM1wf6sZknFXsKDF+c+dHgVKBmf4JoqmHMb/Va8OTK4vSeAhThpW9vwdsPociJ1ATD/zU7bqyZyVtdKMWHIXH0SJ3/RrWn05hn5t5jeeZN+OyQdtPMFbA77i1/f9dE7cy/+RS10G7EbRX4fL42OvQGAoFgT6uM2uPnjHhq9iNeTABjY2Mv6fR5IpGY2Cbg9XqPUr/PZrMNOJ1Oq65pfCQSwcPwK1TtE9F7OYCurgsQRbGQSqWUfD7/lPKfJZPJWc7j8ZzkeX7S5XLZHA6HIEkSqBCam5uxYqnDwf02WDeTiMVikGUZdrsdq6urOhWSCSGdFhoIud3ulrKyMiGbzRrXVqX9j8fj8Pu7UXO4EiPDIZYdNDN7F6DvhKf7+HQ6bRGoaju970bm/2CZmCXn0nAcyBn+xsbG1joTooJsbxv71LDNhUJh299lpPnFNaxt/hVjlZWCPTIar+YEQXhEzzxobk9HRyeWrC2oqhRRnplENBrd0UKa5PEfAQYAH6s95RSa3ooAAAAASUVORK5CYII=">' .
			($this->user->isLoggedIn() ? 'Logged in (' . $this->user->getIdentity()->getId() . ')' : 'Guest');
	}



	/**
	 * Renders HTML code for custom panel
	 * IDebugPanel
	 * @return void
	 */
	public function getPanel()
	{
		ob_start();
		$template = parent::getTemplate();
		if ($this->user->isLoggedIn()) {
			$template->setFile(__DIR__ . '/bar.user.panel.phtml');
		} else {
			$template->setFile(__DIR__ . '/bar.user.panel.guest.phtml');
		}
		$template->registerFilter(new LatteFilter());
		$template->user = $this->user;
		$template->render();

		return ob_get_clean();
	}



	/**
	 * IDebugPanel
	 * @return string
	 */
	public function getId()
	{
		return __CLASS__;
	}



	/**
	 * Registers panel to Debug bar
	 */
	public static function register()
	{
		Debug::addPanel(new self);
	}



	/**
	 * Sign in form component factory.
	 * @return Nette\Application\AppForm
	 */
	public function createComponentLogInForm($name)
	{
		$form = new \Nette\Application\AppForm($this, $name);

		$form->addText('username', 'Username:')
			->addRule(\Nette\Application\AppForm::FILLED, 'Please provide a username.');

		$form->addPassword('password', 'Password:')
			->addRule(\Nette\Application\AppForm::FILLED, 'Please provide a password.');

		$form->addSubmit('send', 'Log in');

		$form->onSubmit[] = callback($this, 'onLogInFormSubmitted');
		return $form;
	}



	public function onLogInFormSubmitted($form)
	{
		try {
			$values = $form->getValues();
			\Nette\Environment::getUser()->login($values['username'], $values['password']);
			$this->redirect('this');
		} catch (AuthenticationException $e) {
			$form->addError($e->getMessage());
		}
	}



	public function handleLogout()
	{
		\Nette\Environment::getUser()->logout();
		$this->redirect('this');
	}
}
