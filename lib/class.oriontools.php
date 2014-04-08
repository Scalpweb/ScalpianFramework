<?php

class OrionTools
{
    /**
     * Convert size to human readable version
     * @param $size
     * @return string
     */
    static public function humanSize($size)
    {
        $unit=array('b','kb','mb','gb','tb','pb');
        return @round($size/pow(1024,($i=floor(log($size,1024)))),2).' '.$unit[$i];
    }

    /**
     * Set array values as keys
     * @param $array
     * @return array
     */
    static public function setValueAsKey($array)
    {
        $result = array();
        foreach($array as $a)
            $result[$a] = $a;
        return $result;
    }

    /**
     * Merge arrays of two html attributes array
     * @param $a1
     * @param $a2
     * @return array
     */
    static public function mergeAttributes($a1, $a2)
    {
        foreach($a2 as $k=>$v)
        {
            if(isset($a1[$k]))
                $a1[$k] = $a1[$k].' '.$v;
            else
                $a1[$k] = $v;
        }
        return $a1;
    }

    /**
     * @param $str
     * @param int $t
     * @param bool $br
     * @return string
     */
    static public function linef($str, $t = 0, $br = false)
    {
        for($i = 0; $i<$t; $i++)
            $str = "\t".$str;
        return (is_string($str) ? $str : print_r($str, true)).($br?'<br />':'')."\n";
    }

    /**
     * Print a model object
     * @param $object
     * @param int $max_depth
     * @param bool $return_as_string
     * @param bool $html_pre_tag
     * @param array $recursiveList
     * @param bool $no_query Set to true to display the object "as is"
     * @internal param int $depth
     * @return string
     */
    static public function print_r($object, $max_depth = 4, $return_as_string = false, $html_pre_tag = false, $recursiveList = array(), $no_query = false)
    {
        $final = OrionTools::func_print_r($object, $max_depth, $return_as_string, $html_pre_tag, $recursiveList, 0, $no_query);
        $final = ($html_pre_tag?'<pre>':'').$final.($html_pre_tag?'</pre>':'');

        if($return_as_string)
            return $final;
        else echo $final;
    }
    static private function func_print_r($object, $max_depth = 4, $return_as_string = false, $html_pre_tag = false, $recursiveList = array(), $depth = 0, $no_query = false)
    {
        if(is_null($object))
            return 'null';

        if(!is_array($object) && !is_object($object))
            return $object;

        $result = '';
        if($depth > 0)
            $result = "\n";
        $result .= OrionTools::linef((is_array($object) ? 'Array' : get_class($object)) . ' (', $depth);
        $depth = $depth + 1;
        if(!in_array($object, $recursiveList))
        {
            if(is_a($object, 'Record') || get_parent_class($object) == 'Record')
            {
                $rows = $object->getTable()->getRows();
                foreach($rows as $v=>$row)
                {
                    if($no_query && !$object->hasPreviouslyLoaded($v))
                        continue;
                    if(!is_object($object->$v))
                    {
                        $result .= OrionTools::linef($v." => ".$object->$v, $depth);
                    }
                    else
                    {
                        if($depth < $max_depth || ($max_depth == 0 && $no_query))
                            $result .= OrionTools::linef($v." => ".OrionTools::func_print_r($object->$v, $max_depth, $return_as_string, $html_pre_tag, $recursiveList, $depth + 1, $no_query), $depth);
                        else
                            $result .= OrionTools::linef($v." => [Record] ".get_class($object->$v), $depth);

                    }
                }
                $links = $object->getTable()->getRelations();
                foreach($links as $v=>$relation)
                {
                    if($no_query && !$object->hasPreviouslyLoaded($v))
                        continue;
                    if(!is_array($object->$v))
                    {
                        if(!is_object($object->$v))
                            $result .= OrionTools::linef($v." => ".$object->$v, $depth);
                        else
                        {
                            if($depth < $max_depth || ($max_depth == 0 && $no_query))
                                $result .= OrionTools::linef($v." => ".OrionTools::func_print_r($object->$v, $max_depth, $return_as_string, $html_pre_tag, $recursiveList, $depth + 1, $no_query), $depth);
                            else
                                $result .= OrionTools::linef($v." => [Record] ".get_class($object->$v), $depth);
                        }
                    }
                    else
                    {
                        if($depth < $max_depth || ($max_depth == 0 && $no_query))
                            $result .= OrionTools::linef($v." => ".OrionTools::func_print_r($object->$v, $max_depth, $return_as_string, $html_pre_tag, $recursiveList, $depth + 1, $no_query), $depth);
                        else
                            $result .= OrionTools::linef($v." => [Array] (".sizeof($object->$v).")", $depth);

                    }
                }
            }
            foreach($object as $k=>$v)
                $result .= OrionTools::linef($k." => ".OrionTools::func_print_r($v, $max_depth, $return_as_string, $html_pre_tag, $recursiveList, $depth + 1, $no_query), $depth);
        }
        else
            $result .= OrionTools::linef('RECURSIVE', $depth);
        $recursiveList[] = $object;

        return $result.OrionTools::linef(')', $depth-1);
    }

}