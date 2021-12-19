<?php

class MyAuthorizator implements Nette\Security\Authorizator
{
	public function isAllowed($role, $resource, $operation): bool
	{
		if ($role === 'admin') {
			return true;
		}
		if ($role === 'user' && $resource === 'article') {
			return true;
		}

////////////////////////////////////////////////////////////////////////////

		$acl = new Nette\Security\Permission;

		$acl->addRole('guest');
		$acl->addRole('registered', 'guest'); // 'registered' dědí od 'guest'
		$acl->addRole('admin', 'registered'); // a od něj dědí 'admin'


		$acl->addResource('article');
		$acl->addResource('comment');
		$acl->addResource('poll');
		

		// nechť guest může prohlížet články, komentáře i ankety
		$acl->allow('guest', ['article', 'comment', 'poll'], 'view');
		// a v anketách navíc i hlasovat
		$acl->allow('guest', 'poll', 'vote');

		// registrovaný dědí práva od guesta, dáme mu navíc právo komentovat
		$acl->allow('registered', 'comment', 'add');

		// administrátor může prohlížet a editovat cokoliv
		$acl->allow('admin', $acl::ALL, ['view', 'edit', 'add']);


		// administrátor nemůže editovat ankety, to by bylo nedemokratické
		$acl->deny('admin', 'poll', 'edit');
		

		// může guest prohlížet články?
		$acl->isAllowed('guest', 'article', 'view'); // true

		// může guest editovat články?
		$acl->isAllowed('guest', 'article', 'edit'); // false

		// může guest hlasovat v anketách?
		$acl->isAllowed('guest', 'poll', 'vote'); // true

		// může guest komentovat?
		$acl->isAllowed('guest', 'comment', 'add'); // false
////////////////////////////////////////////////////////////////////////////

		return false;
	}
}