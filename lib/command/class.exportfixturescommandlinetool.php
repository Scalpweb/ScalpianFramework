<?php

class ExportFixturesCommandLineTool implements ICommandLineTool
{

	public function execute($option)
	{
		if (!CommandLineHandler::askYesNo("Are you sure you want to export your database content as fixtures ?"))
		{
			CommandLineHandler::line("Leaving...");
			return;
		}

		// Finding all databases
		$databases = Database::findDatabases();

		CommandLineHandler::line("");
		CommandLineHandler::line("");
		CommandLineHandler::line("====================================");
		CommandLineHandler::line("Database Exporter");
		CommandLineHandler::line("====================================");
		CommandLineHandler::line("");
		CommandLineHandler::line("");


		CommandLineHandler::line("Databases found: " . sizeof($databases));
		CommandLineHandler::line("");
		foreach ($databases as $db)
		{
			$db = Database::getDatabase($db);
			$this->export($db);
		}
		CommandLineHandler::line("");
		CommandLineHandler::line("Finished");
		CommandLineHandler::line("");
	}

	/**
	 * Call the exporter from outside of command tool
	 */
	public function callFromAction()
	{
		$databases = Database::findDatabases();
		foreach ($databases as $db)
		{
			$db = Database::getDatabase($db);
			$this->export($db);
		}
	}

	/**
	 * Clear old export files
	 *
	 * @param $maxCount
	 */
	public function clearOldExports($maxCount)
	{
		$databases = Database::findDatabases();
		foreach ($databases as $db)
		{
			echo $db."<br />";
			$db = Database::getDatabase($db);
			$files = $db->findExportsFiles();
			$sorted = array();
			foreach($files as $file)
			{
				$sorted[$file] = FileSystem::fileMTime($file);
			}
			asort($sorted);
			print_r($sorted);
			while(sizeof($sorted) > $maxCount)
			{
				$keys = array_keys($sorted);
				FileSystem::deleteFile($keys[0]);
				unset($sorted[$keys[0]]);
			}
		}
	}

	public function export(Database $db)
	{
		$path = ORION_MODELS_DIR . '/' . strtolower($db->getName()) . '/export/';
		$xml = OrionTools::linef('<?xml version="1.0" encoding="ISO-8859-1" ?>');
		$xml .= OrionTools::linef('<records>');
		$count = 0;
		foreach($db->getTables() as $table)
		{
			$tableCount = 0;
			$rows = $table->getRows();
			CommandLineHandler::line(" = Exporting " . $table->getName());
			foreach($table->fetchAll() as $record)
			{
				$xml .= OrionTools::linef('<record database="'.strtolower($db->getName()).'" table="'.strtolower($table->getName()).'">', 1);
				foreach($rows as $row)
				{
					if($row->getName() == 'id')
						continue;
					$dataValue = $record->__get($row->getName());
					$dataValue = str_replace('&', '&amp;', htmlentities($dataValue));
					$xml .= OrionTools::linef('<row name="'.$row->getName().'">'.$dataValue.'</row>', 2);
				}
				$xml .= OrionTools::linef('</record>', 1);
				$tableCount++;
				if($tableCount % 100 == 0)
					CommandLineHandler::line(" === " . $tableCount." records exported");
			}
			$count += $tableCount;
			CommandLineHandler::line(" = Exported: " . $tableCount);
			CommandLineHandler::line("");
		}
		$xml .= OrionTools::linef('</records>');
		FileSystem::writeFile($path.'export.'.date('Y-m-d-H-i-s').'.xml', $xml, false);
		CommandLineHandler::line("");
		CommandLineHandler::line("Database: " . $db->getName());
		CommandLineHandler::line("Records exported: ".$count);
		CommandLineHandler::line("");
	}

	public function getName()
	{
		return 'ExportFixtures';
	}

	public function getDescription()
	{
		return 'Export databases data as xml.';
	}

}