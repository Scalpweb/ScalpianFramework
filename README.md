ScalpianFramework
=================

MVC Php Framework

(Note: I know that using Singleton that much is a design failure. I'm currently working on replacing them by a Dependency Injection container)

Cheatsheet:
===========

$user = Table::getTable('users')->getOneById(1);<br />
// OR<br />
$user = new users(1);<br />
<br />
// Create user and related group<br />
$user = new users();<br />
$user->login = 'JohnDoe';<br />
$user->groups = new groups()<br />
$user->groups->name = 'Test';<br />
$user->save();<br />
<br />
// Query builder<br />
$query = Database::getDatabase('db')->createQuery();<br />
$query = $query->addSelect('*')<br />
               ->addFrom(Table::getTable('users'))<br />
               ->addWhere('id = :_id')<br />
               ->join(Table::getTable('groups'))<br />
               ->execute(QueryResultType::Record, array('id' => 1));<br />
OrionTools::print_r($query);
