<?php

abstract class LoggerEntry
{
    const MESSAGE  = 'Message';
    const NOTICE   = 'Notice';
    const WARNINGS = 'Warning';
    const ERROR    = 'Error';
    const DATABASE = 'Database';
    const ROUTER   = 'Router';
    const CURL     = 'Curl';
    const CACHE    = 'Cache';
}

class Logger extends Singleton
{

    private $entries = array(), $cli = false, $catchable_error_type = E_ALL, $init_time;

    public function __construct()
    {
        $this->init_time = microtime();
    }

    /**
     * Return all entries filtered by type
     * @param $_type
     * @return array
     */
    public function getEntriesByType($_type)
    {
        $items = array();
        foreach($this->entries as $entry)
            if($entry[0] == $_type)
                $items[] = $entry;
        return $items;
    }

    /**
     * @param IErrorHandler $errorHandler
     */
    public function setErrorHandler(IErrorHandler $errorHandler)
    {
        set_error_handler (array($errorHandler, 'handleError'), $this->catchable_error_type);
    }

    /**
     * Set which error typ must be handle by the logger
     * @param $types
     */
    public function setCatchableErrorType($types)
    {
        $this->catchable_error_type = $types;
    }

    /**
     * Add new entry to the logger
     * @param $type Type of entry (from LoggerEntry enumeration)
     * @param $sender Message sender
     * @param $message Content of the message to be logged
     */
    public function log($type, $sender, $message)
    {
        $this->entries[] = array($type, $sender, $message, microtime());
    }

    /**
     * If set to true, Logger will now handle output as text only
     * @param bool $value
     */
    public function setCli($value)
    {
        $this->cli = $value;
    }

    /**
     *  Echo all entries to buffer
     * @param bool $html If sets to true, echoed message will use html
     */
    public function echoEntries($html = true)
    {
        foreach($this->entries as $entry)
        {
            echo ($html?'<strong>':'').$this->entryTypeToString($entry[0]).' - '.$entry[1].' at '.$this->microDateTime($entry[3]).($html?'</strong><br />':'')."\n";
            echo ($html?'<p>':'').$entry[2].($html?'</p><br />':'')."\n";
        }
    }

    /**
     * Format entry time
     * @param $time
     * @return string
     */
    public function microDateTime($time)
    {
        list($microSec, $timeStamp) = explode(" ", $time);
        return date('H:i:', $timeStamp) . (date('s', $timeStamp) + $microSec);
    }

    /**
     * Returns message type integer to string
     * @param $type Type of message
     * @return string
     */
    public function entryTypeToString($type)
    {
        switch($type)
        {
            case 0:     return 'Message';
            case 1:     return 'Notice';
            case 2:     return 'Warning';
            case 4:     return 'Database';
            case 5:     return 'Router';
            case 7:     return 'Curl';
            case 8:     return 'Cache';
            default:    return 'Error';
        }
    }

    /**
     * Build HTML logger console
     */
    public function buildConsole()
    {
        AssetsHelper::addCss('logger.css');
        AssetsHelper::addJs('logger.js');

        $html = OrionTools::linef('<div class="logger_console">');

        $html .= OrionTools::linef('<div class="logger_title">');
        $html .= OrionTools::linef('<a href="" onclick="logger_js_show_panel(\'logger_block_log\');  return false;"><span class="logger_icon"></span>Log</a>');
        $html .= OrionTools::linef('<a href="" onclick="logger_js_show_panel(\'logger_block_info\'); return false;"><span class="logger_icon logger_icon_info"></span>Info</a>');
        $html .= OrionTools::linef('<a href="" onclick="logger_js_show_panel(\'logger_block_error\');  return false;"><span class="logger_icon logger_icon_error"></span>Error</a>');
        $html .= OrionTools::linef('<a href="" onclick="logger_js_show_panel(\'logger_block_database\');  return false;"><span class="logger_icon logger_icon_db"></span>Database</a>');
        $html .= OrionTools::linef('<a href="" onclick="logger_js_show_panel(\'logger_block_router\');  return false;"><span class="logger_icon logger_icon_router"></span>Router</a>');
        $html .= OrionTools::linef('<a href="" onclick="logger_js_show_panel(\'logger_block_cache\');  return false;"><span class="logger_icon logger_icon_cache"></span>Cache</a>');
        $html .= OrionTools::linef('<a href="" onclick="return false;"><span class="logger_icon logger_icon_time"></span>'.round(microtime() - $this->init_time, 6).'ms</a>');
        $html .= OrionTools::linef('<a href="" onclick="return false;"><span class="logger_icon logger_icon_memory"></span>'.OrionTools::humanSize(memory_get_usage()).'</a>');
        $html .= OrionTools::linef('</div>');

        //
        // LOG
        //
        $html .= OrionTools::linef('<div class="logger_block" id="logger_block_log" style="display: none;">');

        $html .= OrionTools::linef('<h3 onclick="logger_js_toggle(\'logger_server\')"><span class="logger_icon logger_icon_plus"></span> SERVER</h3>');
        $html .= OrionTools::linef('<table class="alternate" id="logger_server" style="display: none;">');
        $html .= OrionTools::linef('<tr><th>Key</th><th>Value</th></tr>');
        foreach($_SERVER as $key => $value)
            $html .= OrionTools::linef('<tr><td>'.$key.'</td><td><pre>'.print_r($value, true).'</pre></td></tr>');
        $html .= OrionTools::linef('</table>');

        $html .= OrionTools::linef('<h3 onclick="logger_js_toggle(\'logger_cookies\')"><span class="logger_icon logger_icon_plus"></span> COOKIES</h3>');
        $html .= OrionTools::linef('<table class="alternate" id="logger_cookies" style="display: none;">');
        $html .= OrionTools::linef('<tr><th>Key</th><th>Value</th></tr>');
        foreach($_COOKIE as $key => $value)
            $html .= OrionTools::linef('<tr><td>'.$key.'</td><td><pre>'.print_r($value, true).'</pre></td></tr>');
        $html .= OrionTools::linef('</table>');

        $html .= OrionTools::linef('<h3 onclick="logger_js_toggle(\'logger_session\')"><span class="logger_icon logger_icon_plus"></span> SESSION</h3>');
        $html .= OrionTools::linef('<table class="alternate" id="logger_session" style="display: none;">');
        $html .= OrionTools::linef('<tr><th>Key</th><th>Value</th></tr>');
        foreach($_SESSION as $key => $value)
            $html .= OrionTools::linef('<tr><td>'.$key.'</td><td><pre>'.print_r($value, true).'</pre></td></tr>');
        $html .= OrionTools::linef('</table>');

        $html .= OrionTools::linef('<h3 onclick="logger_js_toggle(\'logger_post\')"><span class="logger_icon logger_icon_plus"></span> POST</h3>');
        $html .= OrionTools::linef('<table class="alternate" id="logger_post" style="display: none;">');
        $html .= OrionTools::linef('<tr><th>Key</th><th>Value</th></tr>');
        foreach($_POST as $key => $value)
            $html .= OrionTools::linef('<tr><td>'.$key.'</td><td>'.OrionTools::print_r($value, 4, true, true).'</td></tr>');
        $html .= OrionTools::linef('</table>');

        $html .= OrionTools::linef('<h3 onclick="logger_js_toggle(\'logger_get\')"><span class="logger_icon logger_icon_plus"></span> GET</h3>');
        $html .= OrionTools::linef('<table class="alternate" id="logger_get" style="display: none;">');
        $html .= OrionTools::linef('<tr><th>Key</th><th>Value</th></tr>');
        foreach($_GET as $key => $value)
            $html .= OrionTools::linef('<tr><td>'.$key.'</td><td>'.OrionTools::print_r($value, 4, true, true).'</td></tr>');
        $html .= OrionTools::linef('</table>');

        $html .= OrionTools::linef('</div>');

        //
        // INFO
        //
        $html .= OrionTools::linef('<div class="logger_block" id="logger_block_info" style="display: none;">');
        $html .= OrionTools::linef('<h3><span class="logger_icon logger_icon_info"></span> Information messages</h3>');
        $html .= OrionTools::linef('<table>');
        $html .= OrionTools::linef('<tr><th>Key</th><th>Source</th><th>Time</th></tr>');
        foreach($this->getEntriesByType(LoggerEntry::MESSAGE) as $entry)
        {
            $html .= OrionTools::linef('<tr><td>'.$entry[0].'</td><td>'.$entry[1].'</td><td>'.$this->microDateTime($entry[3]).'</td></tr>');
            $html .= OrionTools::linef('<tr><td colspan="3">'.$entry[2].'</td></tr>');
        }
        $html .= OrionTools::linef('</table>');
        $html .= OrionTools::linef('</div>');
        $html .= OrionTools::linef('</div>');

        //
        // ERROR
        //
        $html .= OrionTools::linef('<div class="logger_block" id="logger_block_error" style="display: none;">');
        $html .= OrionTools::linef('<h3><span class="logger_icon logger_icon_error"></span> Error messages</h3>');
        $html .= OrionTools::linef('<table>');
        $html .= OrionTools::linef('<tr><th>Key</th><th>Source</th><th>Time</th></tr>');
        foreach($this->getEntriesByType(LoggerEntry::ERROR) as $entry)
        {
            $html .= OrionTools::linef('<tr><td>'.$entry[0].'</td><td>'.$entry[1].'</td><td>'.$this->microDateTime($entry[3]).'</td></tr>');
            $html .= OrionTools::linef('<tr><td colspan="3">'.$entry[2].'</td></tr>');
        }
        $html .= OrionTools::linef('</table>');
        $html .= OrionTools::linef('</div>');
        $html .= OrionTools::linef('</div>');

        //
        // DATABASE
        //
        $html .= OrionTools::linef('<div class="logger_block" id="logger_block_database" style="display: none;">');
        $html .= OrionTools::linef('<h3><span class="logger_icon logger_icon_db"></span> Database messages</h3>');
        $html .= OrionTools::linef('<table>');
        $html .= OrionTools::linef('<tr><th>Key</th><th>Source</th><th>Time</th></tr>');
        foreach($this->getEntriesByType(LoggerEntry::DATABASE) as $entry)
        {
            $html .= OrionTools::linef('<tr><td>'.$entry[0].'</td><td>'.$entry[1].'</td><td>'.$this->microDateTime($entry[3]).'</td></tr>');
            $html .= OrionTools::linef('<tr><td colspan="3">'.$entry[2].'</td></tr>');
        }
        $html .= OrionTools::linef('</table>');
        $html .= OrionTools::linef('</div>');
        $html .= OrionTools::linef('</div>');

        //
        // ROUTER
        //
        $html .= OrionTools::linef('<div class="logger_block" id="logger_block_router" style="display: none;">');
        $html .= OrionTools::linef('<h3><span class="logger_icon logger_icon_router"></span> Router messages</h3>');
        $html .= OrionTools::linef('<table>');
        $html .= OrionTools::linef('<tr><th>Key</th><th>Source</th><th>Time</th></tr>');
        foreach($this->getEntriesByType(LoggerEntry::ROUTER) as $entry)
        {
            $html .= OrionTools::linef('<tr><td>'.$entry[0].'</td><td>'.$entry[1].'</td><td>'.$this->microDateTime($entry[3]).'</td></tr>');
            $html .= OrionTools::linef('<tr><td colspan="3">'.$entry[2].'</td></tr>');
        }
        $html .= OrionTools::linef('</table>');
        $html .= OrionTools::linef('</div>');
        $html .= OrionTools::linef('</div>');

        //
        // CACHE
        //
        $html .= OrionTools::linef('<div class="logger_block" id="logger_block_cache" style="display: none;">');
        $html .= OrionTools::linef('<h3><span class="logger_icon logger_icon_cache"></span> Cache messages</h3>');
        $html .= OrionTools::linef('<table>');
        $html .= OrionTools::linef('<tr><th>Key</th><th>Source</th><th>Time</th></tr>');
        foreach($this->getEntriesByType(LoggerEntry::CACHE) as $entry)
        {
            $html .= OrionTools::linef('<tr><td>'.$entry[0].'</td><td>'.$entry[1].'</td><td>'.$this->microDateTime($entry[3]).'</td></tr>');
            $html .= OrionTools::linef('<tr><td colspan="3">'.$entry[2].'</td></tr>');
        }
        $html .= OrionTools::linef('</table>');
        $html .= OrionTools::linef('</div>');
        $html .= OrionTools::linef('</div>');

        echo $html;
    }

}