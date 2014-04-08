<?php

abstract class QueryResultType
{
    const PDO_OBJECT    = 0;
    const PDO_ARRAY     = 1;
    const RECORD_OBJECT = 2;
    const NONE          = 3;
}