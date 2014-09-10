ScalpianFramework
=================

MVC Php Framework

(Note: I know that using Singleton that much is a design failure. I'm currently working on replacing them by a Dependency Injection container)

Cheatsheet:
===========

$user = Table::getTable('users')->getOneById(1);
// OR
$user = new users(1);

// Create user and related group
$user = new users();
$user->login = 'JohnDoe';
$user->groups = new groups()
$user->groups->name = 'Test';
$user->save();

// Query builder
$query = Database::getDatabase('db')->createQuery();
$query = $query->addSelect('*')
               ->addFrom(Table::getTable('users'))
               ->addWhere('id = :_id')
               ->join(Table::getTable('groups'))
               ->execute(QueryResultType::Record, array('id' => 1));
OrionTools::print_r($query);
