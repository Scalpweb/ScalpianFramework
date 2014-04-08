<?php

class CronManager
{

    /**
     * Returns crons count
     */
    static public function count()
    {
        $db = Manager::getInstance()->getDatabase();
        if($db === null)
            return 0;
        $table = $db->getTable('crons', false);
        return $table === null ? 0 : $table->count();
    }

}