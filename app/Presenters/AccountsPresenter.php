<?php

namespace App\Presenters;

use Nette;
use App\Model\DbManager;
use Nette\Application\UI\Form;
use Latte;

final class AccountsPresenter extends Nette\Application\UI\Presenter
{
    public function startup()
    {
        parent::startup();

        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Homepage:default');
        }
    }

    private DbManager $database;

	public function __construct(DbManager $database)
	{
		$this->database = $database;
	}

    // account manager /////////////////////////////////////////////////////////////////////////////////////////
    public function renderAccountManager(): void{
        $this->template->accounts = $this->database
            ->getUsers();
    }

    // edit authorization /////////////////////////////////////////////////////////////////////////////////////////
    public $editAuthId = null;

    public function renderEditAuthorization(int $userId, int $delete = null){
        if($delete != null){
            $account = $this->database
                ->getUsers()
                ->where("id", $userId)
                ->delete();
        }

        $editedUser = $this->database
            ->getUsers()
            ->get($userId);

        $this->template->account = $editedUser;

        $this->editAuthId = $editedUser->authorization;
    }

    protected function createComponentEditAuthorizationForm(): Form
    {
        $form = new Form;

        $form->addText('id', '')
            ->setRequired();

        $authorization = [
            'starterCreator' => 'Tvůrce začátečník',
            'creatorFull' => 'Důvěryhodný Tvůrce',
            'admin' => 'Admin',
            'developer' => 'Developer',
        ];
        
        $form->addSelect('authorization', '', $authorization)
            ->setDefaultValue($this->editAuthId);

        $form->addSubmit('send', 'Změnit oprávnění');
        $form->onSuccess[] = [$this, 'editAuthorizationFormSucceeded'];

        return $form;
    }

    public function editAuthorizationFormSucceeded(\stdClass $values): void
    {
        // db change start
        $userId = $values->id;

        $account = $this->database->getUsers()
            ->get($userId);

        $this->database->updateAuthorization($values->authorization, $userId);
        // db change end

        $me = $this->getUser()->getIdentity();
        $mail = new Nette\Mail\Message;
		$latte = new Latte\Engine;

        // new info db start
        $accountActualInfo = $this->database->getUsers()
            ->get($userId);
        // new info db end

        // email start
		$mail->setFrom('Desetiminutovka.cz <info@deset.cz>')
			->addTo($accountActualInfo->email)
            ->setSubject("Změna vašeho práva na - Desetiminutovka.cz")
            ->setBody("Právě vašemu účtu ( Uživatelské jméno - {$accountActualInfo->username} ) bylo na Desetiminutovka.cz změněno oprávnění na {$accountActualInfo->authorization} osobou - ( Uživatelské jméno - {$me->username} | Přezdívka - {$me->nickname})");

		$mailer = new Nette\Mail\SendmailMailer;
		$mailer->send($mail);
        // email end

        $this->flashMessage("Uživatel |{$account->nickname}| byl úspěšně změněn na hodnost |{$account->authorization}|.", 'success');
        $this->redirect('Accounts:accountManager');
    }
}


