<?php

namespace App\Model;

use Nette;

final class DbManager
{
	use Nette\SmartObject;

	private Nette\Database\Explorer $database;

	public function __construct(Nette\Database\Explorer $database)
	{
		$this->database = $database;
	}

	public function getPosts()
	{
		return $this->database
			->table('posts')
			->order('dateTest DESC');
	}

	public function getUsers()
	{
		return $this->database
			->table('users');
	}

	public function updatePass(string $hashed_pass, int $meId){
		return $this->database
			->query("UPDATE users SET", [
					'password' => $hashed_pass,
				], 'WHERE id = ?', $meId);
	}

	public function updateAuthorization(string $authorization, int $userId){
		return $this->database
			->query("UPDATE users SET", [
					'authorization' => $authorization,
				], 'WHERE id = ?', $userId);
	}
}