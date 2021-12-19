<?php

namespace App\Presenters;

use Nette;
use App\Model\DbManager;
use Nette\Application\UI\Form;


final class AdminPresenter extends Nette\Application\UI\Presenter
{
    private DbManager $database;

	public function __construct(DbManager $database)
	{
		$this->database = $database;
	}

    public function startup()
    {
        parent::startup();

        if (!$this->getUser()->isLoggedIn()) {
            $this->redirect('Homepage:default');
        }
    }
    
    // dashboard page /////////////////////////////////////////////////////////////////////////////////////////
    public int $pageNum = 1;
    private int $itemsPerPage = 5;
    
    public function handleLoadMore(): void {
        $this->pageNum += 1;
        $this->redrawControl("pastPosts");
    }

    public function renderDashboard(): void {
        $this->template->futurePosts = $this->database->getPosts()
		->where( 'dateTest > ?', date('Y-m-d'));

        $this->template->actualPosts = $this->database->getPosts()
		->where( 'dateTest = ?', date('Y-m-d'));
        
        $this->template->pastPosts = $this->database->getPosts()
		->where( 'dateTest < ?', date('Y-m-d'))
        ->page($this->pageNum, $this->itemsPerPage);
    }

    // add new post page /////////////////////////////////////////////////////////////////////////////////////////
    protected function createComponentNewForm(): Form
    {
        $me = $this->getUser()->getIdentity();

        $form = new Form;
        $form->addText('name', 'Titulek:')
            ->setMaxLength(200)
            ->setRequired();

        $form->addTextArea('content', 'Obsah:')
            ->addFilter(function ($value) {
                return str_replace('DROP', '<span style="color: red; font-weight: bold;">**S-Q-L--INJECTION--WARNING**-(zaměň prosím slovo DROP za jiné)</span>', $value);
            })
            ->addFilter(function ($value) {
                return str_replace('TABLE', '<span style="color: red; font-weight: bold;">**S-Q-L--INJECTION--WARNING**-(zaměň prosím slovo TABLE za jiné)</span>', $value);
            })
            ->addFilter(function ($value) {
                return str_replace('SELECT', '<span style="color: red; font-weight: bold;">**S-Q-L--INJECTION--WARNING**-(zaměň prosím slovo SELECT za jiné)</span>', $value);
            })
            ->addFilter(function ($value) {
                return str_replace('ALTER', '<span style="color: red; font-weight: bold;">**S-Q-L--INJECTION--WARNING**-(zaměň prosím slovo ALTER za jiné)</span>', $value);
            })
            // ->addRule($form::PATTERN, '**S-Q-L--WE-WILL-FIND-YOU**', '\d{5}') // NEFUNGUJE ODESLÁNÍ S TÍMTO ŘÁDKEM
            ->setRequired();

        $form->addText('autor', 'Autor:')
            ->setDefaultValue($me->username);

        $form->addText('dateTest', 'Date:')
            ->setType("date")
            ->setRequired();

        $form->addSubmit('send', 'Vytvořit příspěvek');
        $form->onSuccess[] = [$this, 'newFormSucceeded'];

        return $form;
    }

    public function newFormSucceeded(array $values): void
    {
        $post = $this->database->getPosts()
            ->insert($values);

        $this->flashMessage("Příspěvek byl úspěšně publikován.", 'success');
        $this->redirect('Admin:dashboard');
    }
    
    //  Editing page /////////////////////////////////////////////////////////////////////////////////////////
    public $postEditId = null;

    public function renderEditing(int $id = null): void{
        // if($id == null){
        //     $this->redirect("Homepage:default");
        // }

		$post = $this->database->getPosts()
            ->get($id);

        // if(!$post){
        //     $this->error("Chybová hláška!");
        // }

        $this->template->post = $post;
        $this->postEditId = $post->id;
	}

    protected function createComponentEditingForm(): Form
    {
        $httpRequest = $this->getHttpRequest();
        $url = $httpRequest->getUrl();
        $url = explode('/', $url);
        $postId = end($url);

        $posts = $this->database->getPosts()
                   ->get($postId);

        $form = new Form;
        $form->addText('name', 'Titulek:')
            ->setRequired();

        $form->addText('autor', 'Autor:')
            ->setDisabled()
            ->setRequired();

        $form->addText('dateTest', 'Date:')
            ->setType("date")
            ->setRequired();     

        $form->addTextArea('content', 'Content')
            ->setRequired()
	    ->setDefaultValue($posts->content);

        $form->addSubmit('send', 'Aktualizovat příspěvek');
        $form->onSuccess[] = [$this, 'editingFormSucceeded'];

        return $form;
    }

    public function editingFormSucceeded(array $values): void
    {
        $postId = $this->getParameter('id');
        
	    if ($postId) {
            $post = $this->database->getPosts()
                ->get($postId);
            $post->update($values);
        }

        $this->flashMessage("Příspěvek byl úspěšně publikován.", 'success');
        $this->redirect('Admin:dashboard');
    }

    // profil page /////////////////////////////////////////////////////////////////////////////////////////
    protected function createComponentChangePassForm(): Form
    {
        $min_char_password = 8;

        $form = new Form;

        $form->addPassword('oldPassword', 'oldPassword')
            ->setRequired();
            
        $form->addPassword('newPassword', 'newPassword')
        ->setRequired()
        ->addRule($form::MIN_LENGTH, 'Nové heslo musí mít alespoň %d znaků', $min_char_password);
            
        $form->addPassword('newPasswordRepeat', 'newPasswordRepeat')
        ->setRequired()
        ->addRule($form::MIN_LENGTH, 'Heslo znovu musí mít alespoň %d znaků', $min_char_password);

        $form->addSubmit('send', 'Změnit heslo');
        $form->onSuccess[] = [$this, 'changePassFormSucceeded'];

        return $form;
    }

    public function changePassFormSucceeded(\stdClass $values): void
    {
        $meId = $this->getUser()->getIdentity()->getId();

        $oldPassDb = $this->database->getUsers()
            ->get($meId);

        if (password_verify($values->oldPassword, $oldPassDb->password)) {

            $hashed_pass = password_hash($values->newPassword, PASSWORD_DEFAULT);

            if($values->newPassword != $values->newPasswordRepeat){
                $this->flashMessage("Nová hesla se neshodují.", 'error');
                $this->redirect("Admin:profile");
            }

            if($values->newPassword == "123456789" || $values->newPassword == "password" || $values->newPassword == "12345678" || $values->newPassword == "qwertzuiop" || $values->newPassword == "123321123" || $values->newPassword == "password123" || $values->newPassword == "aa12345678"){
                $this->flashMessage("Nové heslo je moc jednoduché.", 'error');
                $this->redirect("Admin:profile");
            }

            $this->database->updatePass($hashed_pass, $meId);
                
            $this->flashMessage("Heslo bylo úspěšně aktualizováno.", 'success');
        } else {
            $this->flashMessage("Špatně zadané staré heslo.", 'error');
        }
    }

    // delete page /////////////////////////////////////////////////////////////////////////////////////////
    public function renderDeleting(int $id, string $deleteIt = null){
        if($deleteIt != null){
            $this->database->getPosts()
                ->where('id', $id)
                ->delete();

            $this->flashMessage('Příspěvek byl úspěšně smazán.', 'success');
            $this->redirect('Admin:dashboard');
        }

        $this->template->posts = $this->database->getPosts()
            ->where("id", $id);
    }

    // account manager /////////////////////////////////////////////////////////////////////////////////////////
    public function renderAccountManager(): void{
        $this->template->accounts = $this->database
            ->table('users');
    }
}


				