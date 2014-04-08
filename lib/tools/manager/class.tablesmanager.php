<?php

class TablesManager
{

    /**
     * Gets the table creation form
     * @param string $action
     * @return Form
     */
    static public function getTableCreationForm($action)
    {
        $form = new Form('name', 'Create a new table', $action);
        $form->setLayout('simpleform');
        $name    = $form->addField('*Name',   new TextField(  $form, 'name'),    array(new RequiredValidator(), new StringValidator()) );
        $charset = $form->addField('Charset', new SelectField($form, 'charset',  OrionTools::setValueAsKey(Database::$CHARSETS)));
        $engine  = $form->addField('Engine',  new SelectField($form, 'engine',   OrionTools::setValueAsKey(Database::$ENGINES)));
        $charset->setValue('utf8');
        $engine->setValue('MyISAM');
        return $form;
    }

    /**
     * Dispatching form result
     * @param $form
     * @param $db
     * @return bool|array
     */
    static public function dispatchTableCreationForm($form, $db)
    {
        if($form->dispatch())
        {
            $form->validate();
            if($form->isValid())
            {
                try
                {
                    TablesManager::createNewTable(
                        $form->getField('name')->getValue(),
                        $form->getField('charset')->getValue(),
                        $form->getField('engine')->getValue(),
                        $db);
                    Cache::getInstance()->delete('Manager/Tables');
                }
                catch(TableAlreadyExistsException $e)
                {
                    User::getInstance()->flash(FlashTypes::ERROR, "Impossible to create the table: a table with that name already exists.");
                    return false;
                }
                catch(Exception $e)
                {
                    User::getInstance()->flash(FlashTypes::ERROR, "Impossible to create the table: an unknown error occurred. Error: <pre>".$e."</pre>");
                    return false;
                }
                return true;
            }
            return false;
        }
        return false;
    }

    /**
     * Gets the table edition form
     * @param $action
     * @param $table
     * @return \Form
     */
    static public function getTableEditionForm($action, $table)
    {
        $form = new Form('edittable', 'Edit table', $action);
        $form->setLayout('simpleform-nobox');
        $form->addField('Charset', new SelectField($form, 'charset',  OrionTools::setValueAsKey(Database::$CHARSETS)))->setValue($table->getCharset())->setAttribute('class', 'styled');
        $form->addField('Engine',  new SelectField($form, 'engine',  OrionTools::setValueAsKey(Database::$ENGINES)))->setValue($table->getEngine())->setAttribute('class', 'styled');
        return $form;
    }

    /**
     * Gets the table column adding form
     * @param $action
     * @param $table
     * @return \Form
     */
    static public function getTableAddColumnForm($action, $table)
    {
        $form = new Form('addcolumntable', 'Add column', $action);
        $form->setLayout('simpleform-nobox');
        $form->addField('*Name',          new TextField(  $form, 'name'),    array(new RequiredValidator(), new StringValidator()) );
        $form->addField('Type',           new SelectField($form, 'type',     OrionTools::setValueAsKey(Row::getRowTypes())))->setValue('INT')->setAttribute('class', 'styled');
        $form->addField('Length/Value',   new TextField(  $form, 'len'))->setLegend("For 'enum' and 'set', please enter escaped values separated by coma. Example: 'one','two','three' ");
        $form->addField('Default',        new TextField  ($form, 'default'))->setValue('');
        $form->addField('Attribute',      new SelectField($form, 'attribute',OrionTools::setValueAsKey( array('', 'BINARY', 'UNSIGNED', 'UNSIGNED ZEROFILL') )))->setValue('')->setAttribute('class', 'styled');
        $form->addField('Null',           new CheckField ($form, 'null'));
        $form->addField('Auto increment', new CheckField ($form, 'auto_increment'));
        return $form;
    }

    /**
     * Gets the table index adding form
     * @param $action
     * @param $table
     * @return \Form
     */
    static public function getTableAddIndexForm($action, $table)
    {
        $form = new Form('addindextable', 'Add index', $action);
        $form->setLayout('simpleform-nobox');
        $form->addField('*Name',          new TextField(  $form, 'name'),    array(new RequiredValidator(), new StringValidator()) );
        $form->addField('Type',           new SelectField($form, 'type',     OrionTools::setValueAsKey(Database::$INDEXES)),    array(new RequiredValidator()))->setValue('INDEX')->setAttribute('class', 'styled');
        $form->addField('*Columns',       new TextField(  $form, 'columns'), array(new RequiredValidator()) )->setLegend("You can add multiple values separated by a coma. Example: id,name");
        return $form;
    }

    /**
     * Gets the table relation adding form
     * @param $action
     * @param $table
     * @return \Form
     */
    static public function getTableAddRelationForm($action, $table)
    {
        $form = new Form('addrelationtable', 'Add relation', $action);
        $form->setLayout('simpleform-nobox');
        $form->addField('Table',      new SelectField($form, 'target',   OrionTools::setValueAsKey($table->getDatabase()->getTablesNames())))->setAttribute('class', 'styled');
        $form->addField('Type',       new SelectField($form, 'type',     OrionTools::setValueAsKey(array(RelationType::OneToMany, RelationType::ManyToMany, RelationType::ManyToOne, RelationType::OneToOne))),    array(new RequiredValidator()))->setValue(RelationType::OneToMany)->setAttribute('class', 'styled');
        $form->addField('Alias',      new TextField(  $form, 'name'),    array(new StringValidator()) )->setLegend("If you do not enter any value, the relation's alias will be set to the name of the targeted table");
        $form->addField('Local',      new SelectField($form, 'local',    OrionTools::setValueAsKey($table->getRowNames())) )->setAttribute('class', 'styled');
        $form->addField('Distant',    new TextField(  $form, 'distant'), array(new StringValidator()) );
        $form->addField('On Delete',  new SelectField($form, 'delete',   OrionTools::setValueAsKey(array(RelationActions::NO_ACTION, RelationActions::CASCADE, RelationActions::RESTRICT, RelationActions::SET_DEFAULT, RelationActions::SET_NULL))))->setValue(RelationActions::NO_ACTION)->setAttribute('class', 'styled');
        $form->addField('On Update',  new SelectField($form, 'update',   OrionTools::setValueAsKey(array(RelationActions::NO_ACTION, RelationActions::CASCADE, RelationActions::RESTRICT, RelationActions::SET_DEFAULT, RelationActions::SET_NULL))))->setValue(RelationActions::NO_ACTION)->setAttribute('class', 'styled');
        $form->addField('Build mirror relation', new CheckField($form, 'mirror'))->setValue(false);
        return $form;
    }

    /**
     * Gets the table column editing form
     * @param $action
     * @param $table
     * @param $column
     * @return \Form
     */
    static public function getColumnEditionForm($action, $table, $column)
    {
        $column = $table->getRow($column);
        $form = new Form('addcolumntable', 'Add column', $action);
        $form->setLayout('simpleform-nobox');
        $form->addField('*Name',          new TextField(  $form, 'name'),    array(new RequiredValidator(), new StringValidator()) )->setValue($column->getName());
        $form->addField('Type',           new SelectField($form, 'type',     OrionTools::setValueAsKey(Row::getRowTypes())))->setValue($column->getType())->setAttribute('class', 'styled');
        $form->addField('Length/Value',   new TextField(  $form, 'len'))->setLegend("For 'enum' and 'set', please enter escaped values separated by coma. Example: 'one','two','three' ")->setValue($column->getLength());
        $form->addField('Default',        new TextField  ($form, 'default'))->setValue($column->getDefault());
        $form->addField('Attribute',      new SelectField($form, 'attribute',OrionTools::setValueAsKey( array('', 'BINARY', 'UNSIGNED', 'UNSIGNED ZEROFILL') )))->setValue($column->getAttribute())->setAttribute('class', 'styled');
        $form->addField('Null',           new CheckField ($form, 'null'))->setValue($column->getNull() === true);
        $form->addField('Auto increment', new CheckField ($form, 'auto_increment'))->setValue($column->getAutoIncrement() === true);
        return $form;
    }

    /**
     * Gets the record creation form
     * @param $action
     * @param $classname
     * @return \Form
     */
    static public function getRecordCreationForm($action, $classname)
    {
        $record = new $classname();
        $form = new Form('addrecordtable', 'Add record', $action);
        $form->setLayout('simpleform');
        $form->fromObject($record, true);
        return $form;
    }

    /**
     * @param $form
     * @param $table
     * @return bool
     */
    static public function dispatchTableAddColumnForm($form, $table)
    {
        if($form->dispatch())
        {
            $form->validate();
            if($form->isValid())
            {
                try
                {
                    TablesManager::addColumn(
                        $form->getField('name')->getValue(),
                        $form->getField('type')->getValue(),
                        $form->getField('len')->getValue(),
                        $form->getField('default')->getValue(),
                        $form->getField('attribute')->getValue(),
                        $form->getField('null')->getValue(),
                        $form->getField('auto_increment')->getValue(),
                        $table);
                    Cache::getInstance()->delete('Manager/Tables');
                }
                catch(Exception $e)
                {
                    User::getInstance()->flash(FlashTypes::ERROR, "Impossible to add a column: an unknown error occurred. Error: <pre>".$e."</pre>");
                    return false;
                }
                return true;
            }
            return false;
        }
        return false;
    }

    /**
     * @param $form
     * @param $table
     * @return bool
     */
    static public function dispatchTableAddIndexForm($form, $table)
    {
        if($form->dispatch())
        {
            $form->validate();
            if($form->isValid())
            {
                try
                {
                    TablesManager::addIndex(
                        $form->getField('name')->getValue(),
                        $form->getField('type')->getValue(),
                        $form->getField('columns')->getValue(),
                        $table);
                    Cache::getInstance()->delete('Manager/Tables');
                }
                catch(Exception $e)
                {
                    User::getInstance()->flash(FlashTypes::ERROR, "Impossible to add an index: an unknown error occurred. Error: <pre>".$e."</pre>");
                    return false;
                }
                return true;
            }
            return false;
        }
        return false;
    }

    /**
     * @param $form
     * @param $table
     * @return bool
     */
    static public function dispatchTableAddRelationForm($form, $table)
    {
        if($form->dispatch())
        {
            $form->validate();
            if($form->isValid())
            {
                try
                {
                    TablesManager::addRelation(
                        $form->getField('target')->getValue(),
                        $form->getField('type')->getValue(),
                        $form->getField('name')->getValue(),
                        $form->getField('local')->getValue(),
                        $form->getField('distant')->getValue(),
                        $form->getField('delete')->getValue(),
                        $form->getField('update')->getValue(),
                        $form->getField('mirror')->getValue(),
                        $table);
                    Cache::getInstance()->delete('Manager/Tables');
                }
                catch(Exception $e)
                {
                    User::getInstance()->flash(FlashTypes::ERROR, "Impossible to add a relation: an unknown error occurred. Error: <pre>".$e."</pre>");
                    return false;
                }
                return true;
            }
            return false;
        }
        return false;
    }

    /**
     * @param $form
     * @param $table
     * @return bool
     */
    static public function dispatchTableEditionForm($form, $table)
    {
        if($form->dispatch())
        {
            $form->validate();
            if($form->isValid())
            {
                try
                {
                    TablesManager::editTable(
                        $form->getField('charset')->getValue(),
                        $form->getField('engine')->getValue(),
                        $table);
                    Cache::getInstance()->delete('Manager/Tables');
                }
                catch(Exception $e)
                {
                    User::getInstance()->flash(FlashTypes::ERROR, "Impossible to edit the table: an unknown error occurred. Error: <pre>".$e."</pre>");
                    return false;
                }
                return true;
            }
            return false;
        }
        return false;
    }

    /**
     * @param $form
     * @param $table
     * @param $column
     * @return bool
     */
    static public function dispatchColumnEditionForm($form, $table, $column)
    {
        if($form->dispatch())
        {
            $form->validate();
            if($form->isValid())
            {
                try
                {
                    TablesManager::editColumn(
                        $form->getField('name')->getValue(),
                        $form->getField('type')->getValue(),
                        $form->getField('len')->getValue(),
                        $form->getField('default')->getValue(),
                        $form->getField('attribute')->getValue(),
                        $form->getField('null')->getValue(),
                        $form->getField('auto_increment')->getValue(),
                        $column,
                        $table);
                    Cache::getInstance()->delete('Manager/Tables');
                }
                catch(Exception $e)
                {
                    User::getInstance()->flash(FlashTypes::ERROR, "Impossible to edit the column: an unknown error occurred. Error: <pre>".$e."</pre>");
                    return false;
                }
                return true;
            }
            return false;
        }
        return false;
    }

    /**
     * @param $form
     * @return bool
     */
    static public function dispatchRecordCreationForm($form)
    {
        if($form->dispatch())
        {
            $form->validate();
            if($form->isValid())
            {
                $form->save();
                return true;
            }
            return false;
        }
        return false;
    }

    /**
     * Create new table
     * @param $name
     * @param $charset
     * @param $engine
     * @param $db
     * @throws TableAlreadyExistsException
     */
    static public function createNewTable($name, $charset, $engine, $db)
    {
        // Try database name:
        try{
            Table::getTable($name);
        }
        catch(Exception $e)
        {
            // Generates xml
            $xml = OrionTools::linef('<table>',2);
            $xml .= OrionTools::linef('<name>'.$name.'</name>',3);
            $xml .= OrionTools::linef('<engine>'.$engine.'</engine>',3);
            $xml .= OrionTools::linef('<charset>'.$charset.'</charset>',3);
            $xml .= OrionTools::linef('<description></description>',3);
            $xml .= OrionTools::linef('<rows></rows>',3);
            $xml .= OrionTools::linef('<relations></relations>',3);
            $xml .= OrionTools::linef('<indexes></indexes>',3);
            $xml .= OrionTools::linef('</table>',2);

            // Add table schema to database xml
            $content = FileSystem::readFile(ORION_MODELS_DIR.'/'.strtolower($db->getName()).'/schema.'.strtolower($db->getName()).'.xml');
            $content = preg_replace('/<database>(.*)<tables>(.*)<\/tables>(.*)/Uis', "<database>\\1<tables>\\2".$xml."</tables>\\3", $content);
            FileSystem::writeFile(ORION_MODELS_DIR.'/'.strtolower($db->getName()).'/schema.'.strtolower($db->getName()).'.xml', $content, false);

            // Create base table class
            $content = OrionTools::linef('<?php');
            $content .= OrionTools::linef('');
            $content .= OrionTools::linef('/**');
            $content .= OrionTools::linef('* This file has been auto-generated by Orion ORM');
            $content .= OrionTools::linef('* WARNING : Please do not edit or erase it');
            $content .= OrionTools::linef('*/');
            $content .= OrionTools::linef('abstract class '.ucfirst($name).'TableBase extends Table');
            $content .= OrionTools::linef('{');
            $content .= OrionTools::linef('protected static $db_name = \''.strtolower($db->getName()).'\';', 1);
            $content .= OrionTools::linef('protected static $tbl_name = \''.strtolower($name).'\';', 1);
            $content .= OrionTools::linef('');
            $content .= OrionTools::linef('public function __construct()', 1);
            $content .= OrionTools::linef('{', 1);
            $content .= OrionTools::linef('parent::__construct("'.strtolower($name).'", Database::getDatabase("'.$db->getName().'"), "'.$engine.'", "'.$charset.'", "");', 2);
            $content .= OrionTools::linef('}', 1);
            $content .= OrionTools::linef('');
            $content .= OrionTools::linef('public function init()', 1);
            $content .= OrionTools::linef('{', 1);
            $content .= OrionTools::linef('$this->addRow("id", RowType::INT, false, "", true, true);', 2);
            $content .= OrionTools::linef('');
            $content .= OrionTools::linef('}', 1);
            $content .= OrionTools::linef('');
            $content .= OrionTools::linef('}');
            FileSystem::writeFile(ORION_MODELS_DIR.'/'.strtolower($db->getName()).'/base/base.'.strtolower($name).'tablebase.php', $content, false);

            // Create core table class if needed
            if(!FileSystem::checkFile(ORION_MODELS_DIR.'/'.strtolower($db->getName()).'/table.'.strtolower($name).'table.php'))
            {
                $content = OrionTools::linef('<?php');
                $content .= OrionTools::linef('');
                $content .= OrionTools::linef('class '.ucfirst($name).'Table extends '.ucfirst($name).'TableBase');
                $content .= OrionTools::linef('{');
                $content .= OrionTools::linef('', 1);
                $content .= OrionTools::linef('}');
                FileSystem::writeFile(ORION_MODELS_DIR.'/'.strtolower($db->getName()).'/table.'.strtolower($name).'table.php', $content, false);
            }

            // Create base record class
            $content = OrionTools::linef('<?php');
            $content .= OrionTools::linef('');
            $content .= OrionTools::linef('/**');
            $content .= OrionTools::linef('* This file has been auto-generated by Orion ORM');
            $content .= OrionTools::linef('* WARNING : Please do not edit or erase it');
            $content .= OrionTools::linef('*/');
            $content .= OrionTools::linef('abstract class '.ucfirst($name).'Base extends Record');
            $content .= OrionTools::linef('{');
            $content .= OrionTools::linef('');
            $content .= OrionTools::linef('public function __construct($_id = 0)', 1);
            $content .= OrionTools::linef('{');
            $content .= OrionTools::linef('parent::__construct("'.strtolower($name).'", Database::getDatabase("'.strtolower($db->getName()).'")->getTable("'.strtolower($name).'"), $_id);', 2);
            $content .= OrionTools::linef('}');
            $content .= OrionTools::linef('');
            $content .= OrionTools::linef('}');
            FileSystem::writeFile(ORION_MODELS_DIR.'/'.strtolower($db->getName()).'/base/base.'.strtolower($name).'base.php', $content, false);

            // -- Record class file :
            if(!FileSystem::checkFile(ORION_MODELS_DIR.'/'.strtolower($db->getName()).'/record.'.strtolower($name).'.php'))
            {
                CommandLineHandler::line("Creating php class for record: ".ucfirst($db->getName()).'.'.ucfirst($name), 1);
                $content = OrionTools::linef('<?php');
                $content .= OrionTools::linef('');
                $content .= OrionTools::linef('class '.ucfirst($name).' extends '.ucfirst($name).'Base');
                $content .= OrionTools::linef('{');
                $content .= OrionTools::linef('', 1);
                $content .= OrionTools::linef('}');
                FileSystem::writeFile(ORION_MODELS_DIR.'/'.strtolower($db->getName()).'/record.'.strtolower($name).'.php', $content, false);
            }

            // Insert table into database class
            $content = FileSystem::readFile(ORION_MODELS_DIR.'/'.strtolower($db->getName()).'/base/base.'.strtolower($db->getName()).'databasebase.php');
            $content = preg_replace('/public function init\(\)(.*)\{(.*)\}/Uis',
                                     "public function init()\\1{".
                                           "\r\n\t\t\$this->addTable(new ".ucfirst($name)."Table());".
                                           "\\2".
                                           "\t\$this->getTable(\"".strtolower($name)."\")->init();\r\n\t}",
                                     $content);
            FileSystem::writeFile(ORION_MODELS_DIR.'/'.strtolower($db->getName()).'/base/base.'.strtolower($db->getName()).'databasebase.php', $content, false);

            // Create table into database
            $classname = ucfirst($name).'Table';
            $table = new $classname();
            $table->init();
            $table->createTable();

            return;
        }
        // If we did not went into the catch block, then the database already exists and we should throw an error:
        throw(new TableAlreadyExistsException("Impossible to create the table: a table with that name already exists."));
    }

    /**
     * Edit table
     * @param $charset
     * @param $engine
     * @param $table
     */
    public static function editTable($charset, $engine, $table)
    {
        $db = strtolower($table->getDatabase()->getName());
        $name = strtolower($table->getName());

        // Edit table schema from database xml
        $content = FileSystem::readFile(ORION_MODELS_DIR.'/'.$db.'/schema.'.$db.'.xml');
        $content = preg_replace('/<table>(.*)<name>'.$table->getName().'<\/name>(.*)<charset>(.*)<\/charset>(.*)<\/table>/Uis',
            "<table>\\1<name>".$table->getName()."</name>\\2<charset>".$charset."</charset>\\4</table>", $content);
        $content = preg_replace('/<table>(.*)<name>'.$table->getName().'<\/name>(.*)<engine>(.*)<\/engine>(.*)<\/table>/Uis',
            "<table>\\1<name>".$table->getName()."</name>\\2<engine>".$engine."</engine>\\4</table>", $content);
        FileSystem::writeFile(ORION_MODELS_DIR.'/'.$db.'/schema.'.$db.'.xml', $content, false);

        // Edit table class
        $content = FileSystem::readFile(ORION_MODELS_DIR.'/'.$db.'/base/base.'.$name.'tablebase.php');
        $content = preg_replace('/parent::__construct\("'.$name.'", Database::getDatabase\("'.$db.'"\), "([a-zA-Z0-9]*)", "([a-zA-Z0-9]*)", ""\);/Uis',
            'parent::__construct("'.$name.'", Database::getDatabase("'.$db.'"), "'.$engine.'", "'.$charset.'", "");', $content);
        FileSystem::writeFile(ORION_MODELS_DIR.'/'.$db.'/base/base.'.$name.'tablebase.php', $content, false);

        // Execute query
        $query = new Query($table->getDatabase());
        $query->fromString("ALTER TABLE ".$name." ENGINE=".$engine.' DEFAULT CHARACTER SET='.$charset)->execute(QueryResultType::NONE);
    }

    /**
     * Deletes a table
     * @param $table
     */
    public static function deleteTable($table)
    {
        $db = strtolower($table->getDatabase()->getName());
        $name = strtolower($table->getName());

        // Execute query
        $query = new Query($table->getDatabase());
        $query->fromString("DROP TABLE ".$name)->execute(QueryResultType::NONE);

        // Delete table schema from database xml
        $content = FileSystem::readFile(ORION_MODELS_DIR.'/'.$db.'/schema.'.$db.'.xml');
        $content = preg_replace('/<table>(.*)<name>'.$name.'<\/name>(.*)<\/table>/Uis', "", $content);
        FileSystem::writeFile(ORION_MODELS_DIR.'/'.$db.'/schema.'.$db.'.xml', $content, false);

        // Delete classes
        FileSystem::deleteFile(ORION_MODELS_DIR.'/'.$db.'/base/base.'.$name.'tablebase.php');
        FileSystem::deleteFile(ORION_MODELS_DIR.'/'.$db.'/base/base.'.$name.'base.php');

        // Delete table into database class
        $content = FileSystem::readFile(ORION_MODELS_DIR.'/'.$db.'/base/base.'.$db.'databasebase.php');
        $content = preg_replace('/\$this->addTable\(new '.ucfirst($name).'Table\(\)\);/Uis', "", $content);
        $content = preg_replace('/\$this->getTable\("'.$name.'"\)->init\(\);/Uis', "", $content);
        FileSystem::writeFile(ORION_MODELS_DIR.'/'.$db.'/base/base.'.$db.'databasebase.php', $content, false);

        Cache::getInstance()->delete('Manager/Tables');
    }

    /**
     * Add a column
     * @param $name
     * @param $type
     * @param $len
     * @param $default
     * @param $attribute
     * @param $null
     * @param $ai
     * @param $table
     */
    public static function addColumn($name, $type, $len, $default, $attribute, $null, $ai, $table)
    {
        $db = strtolower($table->getDatabase()->getName());
        $tbl = strtolower($table->getName());

        // Add column to mysql
        $query = new Query($table->getDatabase());
        $query->fromString("ALTER TABLE ".$tbl." ADD COLUMN ".$name
            .' '.$type
            .($len != '' && $type != 'enum' && $type != 'set' ? '('.$len.')' : '')
            .($type == 'enum' ? '('.$len.')' : '')
            .($type == 'set' ? '('.$len.')' : '')
            .($attribute != ''  ? ' '.$attribute : '')
            .($null ? ' NULL' : ' NOT NULL')
            .($default != '' ? ' DEFAULT "'.$default.'"' : '')
            .($ai ? ' AUTO_INCREMENT' : '')
        )->execute(QueryResultType::NONE);

        // Create xml
        $xml = '<row name="'.$name.'" type="'.$type.'"';
        $xml .= $len != ''                          ? ' length="'.$len.'"' : '';
        $xml .= $default != ''                      ? ' default="'.$default.'"' : '';
        $xml .= $attribute === 'BINARY'             ? ' binary="true"' : '';
        $xml .= $attribute === 'UNSIGNED'           ? ' unsigned="true"' : '';
        $xml .= $attribute === 'UNSIGNED ZEROFILL'  ? ' unsigned="true" zerofill="true"' : '';
        $xml .= $null === true                      ? ' null="true"' : '';
        $xml .= $ai   === true                      ? ' autoIncrement="true"' : '';
        $xml .= $type === 'enum'                    ? ' enum="'.$len.'"' : '';
        $xml .= $type === 'set'                     ? ' set="'.$len.'"' : '';
        $xml .= ' />';

        // Add column to xml schema
        $content = FileSystem::readFile(ORION_MODELS_DIR.'/'.$db.'/schema.'.$db.'.xml');
        $content = preg_replace('/<table>(.*)<name>'.$table->getName().'<\/name>(.*)<rows>(.*)<\/rows>(.*)<\/table>/Uis',
                                 "<table>\\1<name>".$table->getName()."</name>\\2<rows>\\3\r\n\t".$xml."\r\n</rows>\\4</table>", $content);
        FileSystem::writeFile(ORION_MODELS_DIR.'/'.$db.'/schema.'.$db.'.xml', $content, false);

        // Add column to php classes
        $row1 = '$this->addRow("'.$name.'", RowType::'.$type.','
                            .($null === true ? 'true' : ' false').','
                            .($default != '' ? ' "'.$default.'"' : '""').','
                            .($ai === true ? ' true' : ' false').','
                            .'false,'
                            .($len != '' && $type != 'enum' && $type != 'set' ? ' "'.$len.'"' : '""').','
                            .'"",'
                            .'"",'
                            .($attribute === 'UNSIGNED' || $attribute === 'UNSIGNED ZEROFILL' ? 'true' : 'false').','
                            .($attribute === 'UNSIGNED ZEROFILL' ? 'true' : 'false').','
                            .($attribute === 'BINARY' ? 'true' : 'false').','
                            .'false,'
                            .'false,'
                            .($type == 'enum' ? '"'.$len.'"' : '""').','
                            .($type == 'set' ? '"'.$len.'"' : '""').');';
        $content = FileSystem::readFile(ORION_MODELS_DIR.'/'.$db.'/base/base.'.$tbl.'tablebase.php');
        $content = preg_replace('/public function init\(\)(.*)\{(.*)\}/Uis',
                                "public function init()\\1{\r\n\t".$row1."\\2}", $content);
        FileSystem::writeFile(ORION_MODELS_DIR.'/'.$db.'/base/base.'.$tbl.'tablebase.php', $content, false);
    }

    /**
     * Add new relation
     * @param $table
     * @param $type
     * @param $alias
     * @param $local
     * @param $distant
     * @param $ondelete
     * @param $onupate
     * @param $mirror
     * @param $source
     */
    public static function addRelation($table, $type, $alias, $local, $distant, $ondelete, $onupate, $mirror, $source)
    {
        $db = strtolower($source->getDatabase()->getName());
        $tbl = strtolower($source->getName());
        $alias = $alias == $table ? '' : $alias;

        // Add relation to xml schema
        $xml = '<relation type="'.$type.'" table="'.$table.'" local="'.$local.'" distant="'.$distant.'" alias="'.$alias.'" onDelete="'.$ondelete.'" onUpdate="'.$onupate.'" />';
        $content = FileSystem::readFile(ORION_MODELS_DIR.'/'.$db.'/schema.'.$db.'.xml');
        $content = preg_replace('/<table>(.*)<name>'.$source->getName().'<\/name>(.*)<relations>(.*)<\/relations>(.*)<\/table>/Uis',
                                 "<table>\\1<name>".$source->getName()."</name>\\2<relations>\\3\r\n\t".$xml."\r\n</relations>\\4</table>", $content);
        FileSystem::writeFile(ORION_MODELS_DIR.'/'.$db.'/schema.'.$db.'.xml', $content, false);

        // Add column to php classes
        $row1 = '$this->addRelation(RelationType::'.$type.',  Database::getDatabase("'.$db.'")->getTable("'.$table.'"), "'.$local.'",  "'.$distant.'",  "'.$alias.'", RelationActions::'.$ondelete.', RelationActions::'.$onupate.');';
        $content = FileSystem::readFile(ORION_MODELS_DIR.'/'.$db.'/base/base.'.$tbl.'tablebase.php');
        $content = preg_replace('/public function init\(\)(.*)\{(.*)\}/Uis',
            "public function init()\\1{\\2\t".$row1."\r\n\t}", $content);
        FileSystem::writeFile(ORION_MODELS_DIR.'/'.$db.'/base/base.'.$tbl.'tablebase.php', $content, false);

        if($mirror)
        {
            static::addRelation($source->getName(), Relation::getMirrorType($type), $alias, $distant, $local, $ondelete, $onupate, false, $source->getDatabase()->getTable($table));
        }
    }

    /**
     * Add new index
     * @param $name
     * @param $type
     * @param $columns
     * @param $table
     */
    public static function addIndex($name, $type, $columns, $table)
    {
        $db = strtolower($table->getDatabase()->getName());
        $tbl = strtolower($table->getName());

        // Add column to mysql
        $query = new Query($table->getDatabase());
        $query->fromString("CREATE ".($type === IndexType::FULLTEXT || $type === IndexType::SPATIAL || $type === IndexType::UNIQUE ? $type.' ' : '')."INDEX ".$name." ON ".$tbl."(".$columns.")")->execute(QueryResultType::NONE);

        // Create xml
        $xml = '<index name="'.$name.'" type="'.$type.'" fields="'.$columns.'" />';

        // Add column to xml schema
        $content = FileSystem::readFile(ORION_MODELS_DIR.'/'.$db.'/schema.'.$db.'.xml');
        $content = preg_replace('/<table>(.*)<name>'.$table->getName().'<\/name>(.*)<indexes>(.*)<\/indexes>(.*)<\/table>/Uis',
            "<table>\\1<name>".$table->getName()."</name>\\2<indexes>\\3\r\n\t".$xml."\r\n</indexes>\\4</table>", $content);
        FileSystem::writeFile(ORION_MODELS_DIR.'/'.$db.'/schema.'.$db.'.xml', $content, false);

        // Add column to php classes
        $row1 = '$this->addIndex("'.$name.'", IndexType::'.$type.',  "'.$columns.'");';
        $content = FileSystem::readFile(ORION_MODELS_DIR.'/'.$db.'/base/base.'.$tbl.'tablebase.php');
        $content = preg_replace('/public function init\(\)(.*)\{(.*)\}/Uis',
                                 "public function init()\\1{\\2\t".$row1."\r\n\t}", $content);
        FileSystem::writeFile(ORION_MODELS_DIR.'/'.$db.'/base/base.'.$tbl.'tablebase.php', $content, false);
    }

    /**
     * Deletes a column
     * @param $table
     * @param $column
     */
    public static function deleteColumn($table, $column)
    {
        $db = strtolower($table->getDatabase()->getName());
        $name = strtolower($table->getName());

        // Execute query
        $query = new Query($table->getDatabase());
        $query->fromString("ALTER TABLE ".$name." DROP COLUMN ".$column)->execute(QueryResultType::NONE);

        // Delete column schema from database xml
        $content = FileSystem::readFile(ORION_MODELS_DIR.'/'.$db.'/schema.'.$db.'.xml');
        $content = preg_replace('/<table>(.*)<name>'.$name.'<\/name>(.*)<rows>(.*)<row name="'.$column.'"(.*)\/>(.*)<\/rows>(.*)<\/table>/Uis',
                                '<table>\\1<name>'.$name.'</name>\\2<rows>\\3\\5</rows>\\6</table>',
                                $content);
        FileSystem::writeFile(ORION_MODELS_DIR.'/'.$db.'/schema.'.$db.'.xml', $content, false);

        // Delete column from php classes
        $content = FileSystem::readFile(ORION_MODELS_DIR.'/'.$db.'/base/base.'.$name.'tablebase.php');
        $content = preg_replace('/\$this->addRow\("'.$column.'", (.*)\);/Uis', '', $content);
        FileSystem::writeFile(ORION_MODELS_DIR.'/'.$db.'/base/base.'.$name.'tablebase.php', $content, false);

        Cache::getInstance()->delete('Manager/Tables');
    }

    /**
     * Deletes a relation
     * @param $table
     * @param $relation
     */
    public static function deleteRelation($table, $relation)
    {
        $db = strtolower($table->getDatabase()->getName());
        $name = strtolower($table->getName());
        $relation = $table->getRelation($relation);

        // Delete relation schema from database xml
        $content = FileSystem::readFile(ORION_MODELS_DIR.'/'.$db.'/schema.'.$db.'.xml');
        $content = preg_replace('/<table>(.*)<name>'.$name.'<\/name>(.*)<relations>(.*)<relation type="'.$relation->getType().'" table="'.$relation->getTable2()->getName().'" local="'.$relation->getField1().'" distant="'.$relation->getField2().'" alias="'.$relation->getAlias(true).'" onDelete="'.$relation->getOnDeleteAction().'" onUpdate="'.$relation->getOnUpdateAction().'" \/>(.*)<\/relations>(.*)<\/table>/Uis',
            '<table>\\1<name>'.$name.'</name>\\2<relations>\\3\\4</relations>\\5</table>',
            $content);
        FileSystem::writeFile(ORION_MODELS_DIR.'/'.$db.'/schema.'.$db.'.xml', $content, false);

        // Delete relation from php classes
        $content = FileSystem::readFile(ORION_MODELS_DIR.'/'.$db.'/base/base.'.$name.'tablebase.php');
        $content = preg_replace('/\$this->addRelation\(RelationType::'.$relation->getType().',  Database::getDatabase\("'.$db.'"\)->getTable\("'.$relation->getTable2()->getName().'"\), "'.$relation->getField1().'",  "'.$relation->getField2().'",  "'.$relation->getAlias(true).'", RelationActions::'.$relation->getOnDeleteAction().', RelationActions::'.$relation->getOnUpdateAction().'\);/Uis', '', $content);
        FileSystem::writeFile(ORION_MODELS_DIR.'/'.$db.'/base/base.'.$name.'tablebase.php', $content, false);

        Cache::getInstance()->delete('Manager/Tables');
    }

    /**
     * Deletes an index
     * @param $table
     * @param $index
     */
    public static function deleteIndex($table, $index)
    {
        $db = strtolower($table->getDatabase()->getName());
        $name = strtolower($table->getName());

        // Execute query
        $query = new Query($table->getDatabase());
        $query->fromString("ALTER TABLE ".$name." DROP INDEX ".$index)->execute(QueryResultType::NONE);

        // Delete index schema from database xml
        $content = FileSystem::readFile(ORION_MODELS_DIR.'/'.$db.'/schema.'.$db.'.xml');
        $content = preg_replace('/<table>(.*)<name>'.$name.'<\/name>(.*)<indexes>(.*)<index name="'.$index.'"(.*)\/>(.*)<\/indexes>(.*)<\/table>/Uis',
            '<table>\\1<name>'.$name.'</name>\\2<indexes>\\3\\5</indexes>\\6</table>',
            $content);
        FileSystem::writeFile(ORION_MODELS_DIR.'/'.$db.'/schema.'.$db.'.xml', $content, false);

        // Delete index from php classes
        $content = FileSystem::readFile(ORION_MODELS_DIR.'/'.$db.'/base/base.'.$name.'tablebase.php');
        $content = preg_replace('/\$this->addIndex\("'.$index.'"(.*)\);/Uis', '', $content);
        FileSystem::writeFile(ORION_MODELS_DIR.'/'.$db.'/base/base.'.$name.'tablebase.php', $content, false);

        Cache::getInstance()->delete('Manager/Tables');
    }

    /**
     * Edit a column
     * @param $name
     * @param $type
     * @param $len
     * @param $default
     * @param $attribute
     * @param $null
     * @param $ai
     * @param $column
     * @param $table
     */
    public static function editColumn($name, $type, $len, $default, $attribute, $null, $ai, $column, $table)
    {
        $db = strtolower($table->getDatabase()->getName());
        $tbl = strtolower($table->getName());

        // Edit column into mysql
        $query = new Query($table->getDatabase());
        $query->fromString("ALTER TABLE ".$tbl.($name === $column ? " MODIFY COLUMN ".$name : " CHANGE COLUMN ".$column." ".$name)
            .' '.$type
            .($len != '' && $type != 'enum' && $type != 'set' ? '('.$len.')' : '')
            .($type == 'enum' ? '('.$len.')' : '')
            .($type == 'set' ? '('.$len.')' : '')
            .($attribute != ''  ? ' '.$attribute : '')
            .($null ? ' NULL' : ' NOT NULL')
            .($default != '' ? ' DEFAULT "'.$default.'"' : '')
            .($ai ? ' AUTO_INCREMENT' : '')
        )->execute(QueryResultType::NONE);

        // Create xml
        $xml = '<row name="'.$name.'" type="'.$type.'"';
        $xml .= $len != ''                          ? ' length="'.$len.'"' : '';
        $xml .= $default != ''                      ? ' default="'.$default.'"' : '';
        $xml .= $attribute === 'BINARY'             ? ' binary="true"' : '';
        $xml .= $attribute === 'UNSIGNED'           ? ' unsigned="true"' : '';
        $xml .= $attribute === 'UNSIGNED ZEROFILL'  ? ' unsigned="true" zerofill="true"' : '';
        $xml .= $null === true                      ? ' null="true"' : '';
        $xml .= $ai   === true                      ? ' autoIncrement="true"' : '';
        $xml .= $type === 'enum'                    ? ' enum="'.$len.'"' : '';
        $xml .= $type === 'set'                     ? ' set="'.$len.'"' : '';
        $xml .= ' />';

        // Add column to xml schema
        $content = FileSystem::readFile(ORION_MODELS_DIR.'/'.$db.'/schema.'.$db.'.xml');
        $content = preg_replace('/<table>(.*)<name>'.$tbl.'<\/name>(.*)<rows>(.*)<row name="'.$column.'"(.*)\/>(.*)<\/rows>(.*)<\/table>/Uis',
            '<table>\\1<name>'.$tbl.'</name>\\2<rows>\\3'.$xml.'\\5</rows>\\6</table>',
            $content);
        FileSystem::writeFile(ORION_MODELS_DIR.'/'.$db.'/schema.'.$db.'.xml', $content, false);

        // Add column to php classes
        $row1 = '$this->addRow("'.$name.'", RowType::'.$type.','
            .($null === true ? 'true' : ' false').','
            .($default != '' ? ' "'.$default.'"' : '""').','
            .($ai === true ? ' true' : ' false').','
            .'false,'
            .($len != '' && $type != 'enum' && $type != 'set' ? ' "'.$len.'"' : '""').','
            .'"",'
            .'"",'
            .($attribute === 'UNSIGNED' || $attribute === 'UNSIGNED ZEROFILL' ? 'true' : 'false').','
            .($attribute === 'UNSIGNED ZEROFILL' ? 'true' : 'false').','
            .($attribute === 'BINARY' ? 'true' : 'false').','
            .'false,'
            .'false,'
            .($type == 'enum' ? '"'.$len.'"' : '""').','
            .($type == 'set' ? '"'.$len.'"' : '""').');';
        $content = FileSystem::readFile(ORION_MODELS_DIR.'/'.$db.'/base/base.'.$tbl.'tablebase.php');
        $content = preg_replace('/\$this->addRow\("'.$column.'", (.*)\);/Uis', $row1, $content);
        FileSystem::writeFile(ORION_MODELS_DIR.'/'.$db.'/base/base.'.$tbl.'tablebase.php', $content, false);
    }

}