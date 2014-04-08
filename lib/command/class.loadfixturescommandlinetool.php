<?php

class LoadFixturesCommandLineTool implements ICommandLineTool
{

    public function execute($option)
    {
        if(!CommandLineHandler::askYesNo("Are you sure you want to load fixtures ?"))
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


        CommandLineHandler::line("Databases found: ".sizeof($databases));
        CommandLineHandler::line("");
        foreach($databases as $db)
        {
            $db = Database::getDatabase($db);
            $this->loadFixtures($db);
        }
        CommandLineHandler::line("");
        CommandLineHandler::line("Finished");
        CommandLineHandler::line("");
    }

    public function loadFixtures($db)
    {
        $xmls = $db->findFixturesFiles();
        foreach($xmls as $xml)
        {
            $schema = new SimpleXMLElement(FileSystem::readFile($xml));
            foreach($schema as $record)
            {
                $att = $record->attributes();

                $fields = array();
                foreach($record->row as $row)
                {
                    $row_att = $row->attributes();
                    $key = $row_att['name'];
                    $fields[(string)$key] = '\''.addslashes($row_att['value']).'\'';
                }

                $query = new Query(Database::getDatabase((string)$att['database']));
                $query->insert((string)$att['table'], $fields);
                $query->execute();
            }
        }
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