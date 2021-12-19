<?php

namespace App\Presenters;

use Nette;
use Nette\Application\UI\Form;

final class HomepagePresenter extends Nette\Application\UI\Presenter
{
	protected function createComponentSignInForm(): Form
	{
		$form = new Form;
		$form->addText('username', 'Uživatelské jméno:')
			->setRequired('Prosím vyplňte své uživatelské jméno.');

		$form->addPassword('password', 'Heslo:')
			->setRequired('Prosím vyplňte své heslo.');

		$form->addSubmit('send', 'Přihlásit');

		$form->onSuccess[] = [$this, 'signInFormSucceeded'];
		return $form;
	}

    public function signInFormSucceeded(Form $form, \stdClass $values): void
    {
		try {
			$this->getUser()->login($values->username, $values->password);
			$this->redirect("Admin:dashboard");
		} catch (Nette\Security\AuthenticationException $e) {
			$this->flashMessage('Uživatelské jméno nebo heslo je nesprávné.', "error");
		}
    }

	public function actionOut(): void
	{
		$this->getUser()->logout();
		$this->flashMessage('Odhlášení bylo úspěšné.');
		$this->redirect('Homepage:default');
	}
}