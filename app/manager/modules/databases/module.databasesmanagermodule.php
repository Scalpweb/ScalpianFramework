<?php

class DatabasesManagerModule extends Module
{

    /**
     * Databases overview action
     */
    public function actionIndex()
    {
        $this->databases = CacheHelper::get('Manager/Databases', 'Database::findDatabases', array(), CachePeriod::ONE_DAY) ;
    }

    /**
     * Databases creation action
     */
    public function actionNew()
    {
        $this->form = DatabasesManager::getDatabaseCreationForm('/Manager/Databases/New');
        $this->result = DatabasesManager::dispatchDatabaseCreationForm($this->form);
        if($this->result === true)
        {
            User::getInstance()->flash(FlashTypes::SUCCESS, 'The database has been created');
            Router::getInstance()->redirect("/Manager/Databases");
        }
    }

    /**
     * Databases table overview action
     */
    public function actionView($db)
    {
        try
        {
            $this->db = Database::getDatabase($db);
            $this->tables = $this->db->getTables();
        }
        catch(Exception $e)
        {
            User::getInstance()->flash(FlashTypes::ERROR, 'This database does not exist');
            Router::getInstance()->redirect("/Manager/Databases");
        }
    }

    /**
     * Table creation action
     */
    public function actionEdit($db)
    {
        try
        {
            $this->db = Database::getDatabase($db);
            $this->form = DatabasesManager::getDatabaseEditionForm('/Manager/Databases/Edit/'.$db, $this->db);
        }
        catch(Exception $e)
        {
            User::getInstance()->flash(FlashTypes::ERROR, 'This database does not exist');
            Router::getInstance()->redirect("/Manager/Databases");
        }

        $this->result = DatabasesManager::dispatchDatabaseEditionForm($this->form, $this->db);
        if($this->result === true)
        {
            User::getInstance()->flash(FlashTypes::SUCCESS, 'The database has been edited');
            Router::getInstance()->redirect("/Manager/Databases/View/".$db);
        }
    }

    /**
     * Table creation action
     */
    public function actionNewtable($db)
    {
        try
        {
            $this->db = Database::getDatabase($db);
            $this->form = TablesManager::getTableCreationForm('/Manager/Databases/Newtable/'.$db);
        }
        catch(Exception $e)
        {
            User::getInstance()->flash(FlashTypes::ERROR, 'This database does not exist');
            Router::getInstance()->redirect("/Manager/Databases");
        }

        $this->result = TablesManager::dispatchTableCreationForm($this->form, $this->db);
        if($this->result === true)
        {
            User::getInstance()->flash(FlashTypes::SUCCESS, 'The table has been created');
            Router::getInstance()->redirect("/Manager/Databases/View/".$db);
        }
    }

    /**
     * Table edit aciton
     */
    public function actionEdittable($db, $table)
    {
        $this->db = Database::getDatabase($db);
        $this->table = $this->db->getTable($table);
        $this->form         = TablesManager::getTableEditionForm    ('/Manager/Databases/EditTable/'.$db.'/'.$table, $this->table);
        $this->formColumn   = TablesManager::getTableAddColumnForm  ('/Manager/Databases/EditTable/'.$db.'/'.$table, $this->table);
        $this->formRelation = TablesManager::getTableAddRelationForm('/Manager/Databases/EditTable/'.$db.'/'.$table, $this->table);
        $this->formIndex    = TablesManager::getTableAddIndexForm   ('/Manager/Databases/EditTable/'.$db.'/'.$table, $this->table);

        $this->result = TablesManager::dispatchTableEditionForm($this->form, $this->table);
        if($this->result === true)
        {
            User::getInstance()->flash(FlashTypes::SUCCESS, 'The table has been edited');
            Router::getInstance()->redirect("/Manager/Databases/EditTable/".$db."/".$table);
        }

        $this->result = TablesManager::dispatchTableAddColumnForm($this->formColumn, $this->table);
        if($this->result === true)
        {
            User::getInstance()->flash(FlashTypes::SUCCESS, 'The column has been added');
            Router::getInstance()->redirect("/Manager/Databases/EditTable/".$db."/".$table);
        }

        $this->result = TablesManager::dispatchTableAddRelationForm($this->formRelation, $this->table);
        if($this->result === true)
        {
            User::getInstance()->flash(FlashTypes::SUCCESS, 'The relation has been added');
            Router::getInstance()->redirect("/Manager/Databases/EditTable/".$db."/".$table);
        }

        $this->result = TablesManager::dispatchTableAddIndexForm($this->formIndex, $this->table);
        if($this->result === true)
        {
            User::getInstance()->flash(FlashTypes::SUCCESS, 'The index has been added');
            Router::getInstance()->redirect("/Manager/Databases/EditTable/".$db."/".$table);
        }
    }

    /**
     * Delete database action
     */
    public function actionDelete($db)
    {
        $this->db = Database::getDatabase($db);

        try
        {
            DatabasesManager::delete($this->db);
            User::getInstance()->flash(FlashTypes::SUCCESS, 'The database has been deleted');
            Router::getInstance()->redirect("/Manager/Databases");
        }
        catch(Exception $e)
        {
            User::getInstance()->flash(FlashTypes::ERROR, 'Impossible to delete this database: '.$e);
            Router::getInstance()->redirect("/Manager/Databases");
        }
    }

    /**
     * Delete table action
     */
    public function actionDeletetable($db, $table)
    {
        $this->db = Database::getDatabase($db);
        $this->table = $this->db->getTable($table);

        try
        {
            TablesManager::deleteTable($this->table);
            User::getInstance()->flash(FlashTypes::SUCCESS, 'The table has been deleted');
            Router::getInstance()->redirect("/Manager/Databases/View/".$db);
        }
        catch(Exception $e)
        {
            User::getInstance()->flash(FlashTypes::ERROR, 'Impossible to delete this table: '.$e);
            Router::getInstance()->redirect("/Manager/Databases/View/".$db);
        }
    }

    /**
     * Delete table column
     */
    public function actionDeletecolumn($db, $table, $column)
    {
        $this->db = Database::getDatabase($db);
        $this->table = $this->db->getTable($table);

        try
        {
            TablesManager::deleteColumn($this->table, $column);
            User::getInstance()->flash(FlashTypes::SUCCESS, 'The column has been deleted');
            Router::getInstance()->redirect("/Manager/Databases/EditTable/".$db."/".$table);
        }
        catch(Exception $e)
        {
            User::getInstance()->flash(FlashTypes::ERROR, 'Impossible to delete this column: '.$e);
            Router::getInstance()->redirect("/Manager/Databases/EditTable/".$db."/".$table);
        }
    }

    /**
     * Delete table relation
     */
    public function actionDeleterelation($db, $table, $relation)
    {
        $this->db = Database::getDatabase($db);
        $this->table = $this->db->getTable($table);

        try
        {
            TablesManager::deleteRelation($this->table, $relation);
            User::getInstance()->flash(FlashTypes::SUCCESS, 'The relation has been deleted');
            Router::getInstance()->redirect("/Manager/Databases/EditTable/".$db."/".$table);
        }
        catch(Exception $e)
        {
            User::getInstance()->flash(FlashTypes::ERROR, 'Impossible to delete this relation: '.$e);
            Router::getInstance()->redirect("/Manager/Databases/EditTable/".$db."/".$table);
        }
    }

    /**
     * Delete table index
     */
    public function actionDeleteindex($db, $table, $index)
    {
        $this->db = Database::getDatabase($db);
        $this->table = $this->db->getTable($table);

        try
        {
            TablesManager::deleteIndex($this->table, $index);
            User::getInstance()->flash(FlashTypes::SUCCESS, 'The index has been deleted');
            Router::getInstance()->redirect("/Manager/Databases/EditTable/".$db."/".$table);
        }
        catch(Exception $e)
        {
            User::getInstance()->flash(FlashTypes::ERROR, 'Impossible to delete this index: '.$e);
            Router::getInstance()->redirect("/Manager/Databases/EditTable/".$db."/".$table);
        }
    }

    /**
     * Table column edition action
     */
    public function actionEditcolumn($db, $table, $column)
    {
        $this->db = Database::getDatabase($db);
        $this->table = $this->db->getTable($table);
        $this->column = $column;
        $this->form = TablesManager::getColumnEditionForm('/Manager/Databases/EditColumn/'.$db.'/'.$table.'/'.$column, $this->table, $column);

        $this->result = TablesManager::dispatchColumnEditionForm($this->form, $this->table, $column);
        if($this->result === true)
        {
            User::getInstance()->flash(FlashTypes::SUCCESS, 'The column has been edited');
            Router::getInstance()->redirect("/Manager/Databases/EditTable/".$db."/".$table);
        }
    }

    /**
     * Show all records
     * @param $db
     * @param $table
     */
    public function actionRecords($db, $table)
    {
        $this->db = Database::getDatabase($db);
        $this->table = $this->db->getTable($table);
        $this->records = $this->table->fetchAll();
    }

    /**
     * Deletes a record
     * @param $db
     * @param $table
     * @param $id
     */
    public function actionDeleterecord($db, $table, $id)
    {
        $classname = $table;
        $record = new $classname($id);
        $record->delete();
        User::getInstance()->flash(FlashTypes::SUCCESS, 'The record has been deleted');
        Router::getInstance()->redirect("/Manager/Databases/Records/".$db."/".$table);
    }

    /**
     * Adds a new record
     * @param $db
     * @param $table
     */
    public function actionNewrecord($db, $table)
    {
        try
        {
            $this->db = Database::getDatabase($db);
            $this->table = $this->db->getTable($table);
            $this->form = TablesManager::getRecordCreationForm('/Manager/Databases/NewRecord/'.$db.'/'.$table, $table);
        }
        catch(Exception $e)
        {
            User::getInstance()->flash(FlashTypes::ERROR, 'This database or table does not exist');
            Router::getInstance()->redirect("/Manager/Databases/View/".$table);
        }

        $this->result = TablesManager::dispatchRecordCreationForm($this->form);
        if($this->result === true)
        {
            User::getInstance()->flash(FlashTypes::SUCCESS, 'The record has been created');
            Router::getInstance()->redirect("/Manager/Databases/Records/".$db."/".$table);
        }
    }

}