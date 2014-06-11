<?php

class DatabasesManager
{

	/**
	 * Delete a database
	 * @param $db
	 */
	static public function delete($db)
	{
		foreach ($db->getTables() as $table)
		{
			TablesManager::deleteTable($table);
		}

		// Execute query
		$query = new Query($db);
		$query->fromString("DROP DATABASE " . $db->getName())->execute(QueryResultType::NONE);

		// Delete classes
		FileSystem::deleteFile(ORION_MODELS_DIR . '/' . strtolower($db->getName()) . '/base/base.' . strtolower($db->getName()) . 'databasebase.php');
		FileSystem::deleteFile(ORION_MODELS_DIR . '/' . strtolower($db->getName()) . '/schema.' . strtolower($db->getName()) . '.xml');

		Cache::getInstance()->delete('Manager/Tables');
		Cache::getInstance()->delete('Manager/Databases');
		Cache::getInstance()->delete('Manager/DatabaseSize/' . strtolower($db->getName()));
	}

	/**
	 * Gets the database creation form
	 * @param string $action
	 * @return Form
	 */
	static public function getDatabaseCreationForm($action = '')
	{
		$form = new Form('name', 'Create a new database', $action);
		$form->setLayout('simpleform');
		$form->addField('*Name', new TextField($form, 'name'), array(new RequiredValidator(), new StringValidator()));
		$form->addField('*Host', new TextField($form, 'host'), array(new RequiredValidator()));
		$form->addField('*User', new TextField($form, 'user'), array(new RequiredValidator()));
		$form->addField('Password', new TextField($form, 'password'));
		$form->addField('Port', new TextField($form, 'port'), array(new IntegerValidator()));
		$form->addField('Description', new TextField($form, 'description'));
		$charset = $form->addField('Charset', new SelectField($form, 'charset', OrionTools::setValueAsKey(Database::$CHARSETS)))->setAttribute('class', 'styled');
		$charset->setValue('utf8');
		return $form;
	}

	/**
	 * Gets the database edition form
	 * @param string $action
	 * @param Database $database
	 * @return Form
	 */
	static public function getDatabaseEditionForm($action = '', $database)
	{
		$form = new Form('editdatabase', 'Edit database', $action);
		$form->setLayout('simpleform');
		$form->addField('*Host', new TextField($form, 'host'), array(new RequiredValidator()))->setValue($database->getHost());
		$form->addField('*User', new TextField($form, 'user'), array(new RequiredValidator()))->setValue($database->getUser());
		$form->addField('Password', new TextField($form, 'password'))->setValue($database->getPassword());
		$form->addField('Port', new TextField($form, 'port'), array(new IntegerValidator()))->setValue($database->getPort());
		$form->addField('Charset', new SelectField($form, 'charset', OrionTools::setValueAsKey(Database::$CHARSETS)))->setValue($database->getCharset())->setAttribute('class', 'styled');
		return $form;
	}

	/**
	 * Dispatching form result
	 * @param $form
	 * @return bool|array
	 */
	static public function dispatchDatabaseCreationForm($form)
	{
		if ($form->dispatch())
		{
			$form->validate();
			if ($form->isValid())
			{
				try
				{
					DatabasesManager::createNewDatabase(
						$form->getField('name')->getValue(),
						$form->getField('host')->getValue(),
						$form->getField('user')->getValue(),
						$form->getField('password')->getValue(),
						$form->getField('port')->getValue(),
						$form->getField('charset')->getValue(),
						$form->getField('description')->getValue());
					Cache::getInstance()->delete('Manager/Databases');
				} catch (DatabaseAlreadyExistsException $e)
				{
					User::getInstance()->flash(FlashTypes::ERROR, "Impossible to create the database: a database with that name already exists.");
					return false;
				} catch (Exception $e)
				{
					ob_end_clean();
					FileSystem::removeDirectory(ORION_MODELS_DIR . '/' . strtolower($form->getField('name')->getValue()));
					User::getInstance()->flash(FlashTypes::ERROR, "Impossible to create the database: an unknown error occurred. Error: <pre>" . $e . "</pre>");
					return false;
				}
				return true;
			}
			return false;
		}
		return false;
	}

	/**
	 * Dispatching edition form result
	 * @param $form
	 * @param $db
	 * @return bool|array
	 */
	static public function dispatchDatabaseEditionForm($form, $db)
	{
		if ($form->dispatch())
		{
			$form->validate();
			if ($form->isValid())
			{
				DatabasesManager::editDatabase(
					$db,
					$form->getField('host')->getValue(),
					$form->getField('user')->getValue(),
					$form->getField('password')->getValue(),
					$form->getField('port')->getValue(),
					$form->getField('charset')->getValue());
				Cache::getInstance()->delete('Manager/Databases');
				return true;
			}
			return false;
		}
		return false;
	}

	/**
	 * Create a new database
	 * @param $name
	 * @param $host
	 * @param $user
	 * @param $password
	 * @param $port
	 * @param $charset
	 * @param $description
	 * @throws DatabaseAlreadyExistsException
	 */
	static public function createNewDatabase($name, $host, $user, $password, $port, $charset, $description)
	{
		// Try database name:
		try
		{
			Database::getDatabase($name);
		} catch (Exception $e)
		{

			FileSystem::mkdir(ORION_MODELS_DIR . '/' . strtolower($name));
			FileSystem::mkdir(ORION_MODELS_DIR . '/' . strtolower($name) . '/base');
			FileSystem::mkdir(ORION_MODELS_DIR . '/' . strtolower($name) . '/export');
			FileSystem::mkdir(ORION_MODELS_DIR . '/' . strtolower($name) . '/fixture');

			$xml = OrionTools::linef('<?xml version="1.0" encoding="ISO-8859-1" ?>');
			$xml .= OrionTools::linef('<database>');
			$xml .= OrionTools::linef('<name>' . strtolower($name) . '</name>', 1);
			$xml .= OrionTools::linef('<charset>' . ($charset == '' ? 'utf8' : $charset) . '</charset>', 1);
			$xml .= OrionTools::linef('<engine>MySQL</engine>', 1);
			$xml .= OrionTools::linef('<description>' . $description . '</description>', 1);
			$xml .= OrionTools::linef('<host>' . $host . '</host>', 1);
			$xml .= OrionTools::linef('<port>' . $port . '</port>', 1);
			$xml .= OrionTools::linef('<user>', 1);
			$xml .= OrionTools::linef('<username>' . $user . '</username>', 2);
			$xml .= OrionTools::linef('<password>' . $password . '</password>', 2);
			$xml .= OrionTools::linef('</user>', 1);
			$xml .= OrionTools::linef('<tables></tables>', 1);
			$xml .= OrionTools::linef('</database>');
			FileSystem::writeFile(ORION_MODELS_DIR . '/' . strtolower($name) . '/schema.' . strtolower($name) . '.xml', $xml, false);

			BuildDatabaseCommandLineTool::$avoid_confirmation = true;
			BuildDatabaseCommandLineTool::$force_one = strtolower($name);
			$command = new BuildDatabaseCommandLineTool();
			ob_start();
			$command->execute(array());
			ob_end_clean();

			return;
		}
		// If we did not went into the catch block, then the database already exists and we should throw an error:
		throw(new DatabaseAlreadyExistsException("Impossible to create the database: a database with that name already exists."));
	}

	/**
	 * Edit database parameters
	 * @param $db
	 * @param $host
	 * @param $user
	 * @param $password
	 * @param $port
	 * @param $charset
	 */
	static public function editDatabase($db, $host, $user, $password, $port, $charset)
	{
		// Edit values on XML file
		$content = FileSystem::readFile(ORION_MODELS_DIR . '/' . strtolower($db->getName()) . '/schema.' . strtolower($db->getName()) . '.xml');
		$content = preg_replace('/<database>(.*)<host>(.+)<\/host>(.*)<tables>/Uis', "<database>\\1<host>" . $host . "</host>\\3<tables>", $content);
		$content = preg_replace('/<database>(.*)<charset>(.+)<\/charset>(.*)<tables>/Uis', "<database>\\1<charset>" . $charset . "</charset>\\3<tables>", $content);
		$content = preg_replace('/<database>(.*)<username>(.+)<\/username>(.*)<tables>/Uis', "<database>\\1<username>" . $user . "</username>\\3<tables>", $content);
		$content = preg_replace('/<database>(.*)<password>(.+)<\/password>(.*)<tables>/Uis', "<database>\\1<password>" . $password . "</password>\\3<tables>", $content);
		$content = preg_replace('/<database>(.*)<port>(.+)<\/port>(.*)<tables>/Uis', "<database>\\1<port>" . $port . "</port>\\3<tables>", $content);
		FileSystem::writeFile(ORION_MODELS_DIR . '/' . strtolower($db->getName()) . '/schema.' . strtolower($db->getName()) . '.xml', $content, false);

		// Edit value on class file
		$content = FileSystem::readFile(ORION_MODELS_DIR . '/' . strtolower($db->getName()) . '/base/base.' . strtolower($db->getName()) . 'databasebase.php');
		$content = preg_replace('/parent::__construct\((.*)\);/Uis',
			'parent::__construct("' . $db->getName() . '", "' . $host . '", "' . $user . '", "' . $password . '", "' . $port . '", "' . $charset . '", "' . $db->getCollate() . '");',
			$content);
		FileSystem::writeFile(ORION_MODELS_DIR . '/' . strtolower($db->getName()) . '/base/base.' . strtolower($db->getName()) . 'databasebase.php', $content, false);

		// Edit value on db
		if ($charset != $db->getCharset())
			$db->changeCharset($charset);
	}

}