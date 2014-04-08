<?php

class UploadHelper
{

    private $fieldName, $allowed_extensions = array(), $file_max_size = 0;

    public function __construct($field_name)
    {
        $this->fieldName = $field_name;
    }

    /**
     * Add extension filter
     * @param $extension Extension to add to allowed list. If this is an array, all element are added to the list
     */
    public function addExtensionFilter($extension)
    {
        if(is_array($extension))
        {
            foreach($extension as $element)
                $this->allowed_extensions[] = $element;
        }
        else
            $this->allowed_extensions[] = $extension;
    }

    /**
     * Save uploaded file to specified location
     * @param $target_directory Directory where the file must be saved to
     * @param string $target_filename Name of the finale file. Leave blank to keep origin file name
     * @param bool $clean_filename Set to true if you want to automatically clean filename from accentuation and spaces
     * @param int $max_size If set to 0, no size check
     * @return int|bool
     */
    public function doUpload($target_directory, $target_filename = '', $clean_filename = true, $max_size = 0)
    {
        $fileObject = $_FILES[$this->fieldName];
        $fileName = basename($fileObject['name']);
        $fileSize = filesize($fileObject['name']['tmp_name']);
        $fileExtension = strrchr($fileObject['name']['name'], '.');

        if(sizeof($this->allowed_extensions) > 0 && !in_array($fileExtension, $this->allowed_extensions))
            return UploadErrors::WRONG_EXTENSION;

        if($this->file_max_size > 0 && $fileSize > $this->file_max_size)
            return UploadErrors::FILE_IS_TOO_BIG;

        $finalFileName = $fileName;
        if($target_filename !== '')
            $finalFileName = $target_filename;

        if($clean_filename)
            $finalFileName = $this->cleanFileName($finalFileName);

        if(!FileSystem::isWritable($target_directory))
            return UploadErrors::TARGET_DIRECTORY_NOT_WRITABLE;

        if(substr($target_directory, -1) !== '/')
            $target_directory .= '/';

        if(move_uploaded_file($fileObject['tmp_name'], $target_directory.$finalFileName))
            return true;
        else
            return UploadErrors::UNKNOWN_ERROR;
    }

    /**
     * Cleans filename from accentuation and spaces
     * @param $filename
     * @return mixed
     */
    private function cleanFileName($filename)
    {
        $filename = strtr($filename, 'ÀÁÂÃÄÅÇÈÉÊËÌÍÎÏÒÓÔÕÖÙÚÛÜÝàáâãäåçèéêëìíîïðòóôõöùúûüýÿ', 'AAAAAACEEEEIIIIOOOOOUUUUYaaaaaaceeeeiiiioooooouuuuyy');
        return preg_replace('/([^.a-z0-9]+)/i', '-', $filename);
    }

    /**
     * Get the html form attribute needed to file uploading
     * @return string
     */
    static public function getHtmlFormTagAttribute()
    {
        return 'enctype="multipart/form-data"';
    }

}