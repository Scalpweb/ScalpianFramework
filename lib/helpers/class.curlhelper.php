<?php

class CurlHelper
{

    private $url, $return_result, $options = array(), $result_to_file_path = '', $result_to_file_mode = '', $curlInstance;

    public function __construct($_url,$_return_result = true)
    {
        $this->url = $_url;
        $this->return_result = $_return_result;
        $this->curlInstance = curl_init($this->url);
    }

    /**
     * @param $path
     * @param string $mode
     */
    public function writeResultToFile($path, $mode = 'w')
    {
        $this->result_to_file_path = $path;
        $this->result_to_file_mode = $mode;
    }

    /**
     * @param $option
     * @param $value
     */
    public function setOption($option, $value)
    {
        $this->options[$option] = $value;
    }

    /**
     * Execute curl
     * @return bool|mixed
     */
    public function execute()
    {

        if($this->result_to_file_path !== '')
        {
            $fp = fopen($this->result_to_file_path, $this->result_to_file_mode);
            curl_setopt($this->curlInstance, CURLOPT_FILE, $fp);
        }

        foreach($this->options as $key=>$value)
            curl_setopt($this->curlInstance, $key, $value);

        $result = curl_exec($this->curlInstance);
        curl_close($this->curlInstance);

        if($this->result_to_file_path !== '')
        {
            fclose($fp);
        }

        return $this->return_result ? $result : true;
    }

    /**
     * @return resource
     */
    public function getCurlObject()
    {
        return $this->curlInstance;
    }

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

}