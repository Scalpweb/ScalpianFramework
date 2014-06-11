<?php

class LoadFixturesCommandLineTool implements ICommandLineTool
{

	public function execute($option)
	{
		if (!CommandLineHandler::askYesNo("Are you sure you want to load fixtures ?"))
		{
			CommandLineHandler::line("Leaving...");
			return;
		}

		// Finding all databases
		$databases = Database::findDatabases();

		CommandLineHandler::line("");
		CommandLineHandler::line("");
		CommandLineHandler::line("====================================");
		CommandLineHandler::line("Fixtures Loader");
		CommandLineHandler::line("====================================");
		CommandLineHandler::line("");
		CommandLineHandler::line("");


		CommandLineHandler::line("Databases found: " . sizeof($databases));
		CommandLineHandler::line("");
		foreach ($databases as $db)
		{
			$db = Database::getDatabase($db);
			CommandLineHandler::line("Database: " . $db->getName());
			$this->loadFixtures($db);
		}
		CommandLineHandler::line("");
		CommandLineHandler::line("Finished");
		CommandLineHandler::line("");
	}

	public function loadFixtures(Database $db)
	{
		$xmls = $db->findFixturesFiles();
		$count = 0;
		foreach ($xmls as $xml)
		{
			$xmlCount = 0;
			CommandLineHandler::line(" = Reading file: ".basename($xml));
			$schema = new SimpleXMLElement(FileSystem::readFile($xml));
			foreach ($schema as $record)
			{
				$xmlCount ++;
				$att = $record->attributes();

				$fields = array();
				foreach ($record->row as $row)
				{
					$row_att = $row->attributes();
					$key = $row_att['name'];
					if(isset($row_att['value']))
						$value = $row_att['value'];
					else
						$value = html_entity_decode(str_replace('&amp;', '&', $row));
					$fields[(string)$key] = '\'' . addslashes($value) . '\'';
				}

				$query = new Query(Database::getDatabase((string)$att['database']));
				$query->insert((string)$att['table'], $fields);
				$query->execute();
				if($xmlCount % 100 === 0)
					CommandLineHandler::line(" === Loaded: ".$xmlCount."/".sizeof($schema));
			}
			CommandLineHandler::line(" === Loaded: ".$xmlCount."/".sizeof($schema));
			CommandLineHandler::line("");
			$count += $xmlCount;
		}
		CommandLineHandler::line("Total records loaded: ".$count);
	}

	public function getName()
	{
		return 'LoadFixtures';
	}

	public function getDescription()
	{
		return 'Read xml fixtures file for each database, and insert records into database.';
	}

}