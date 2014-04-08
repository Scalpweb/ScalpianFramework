<?php

class Form
{

    private $error_message_formatter, $name, $method, $action, $key, $token, $use_token, $fields = array(), $texts = array(), $errors = array(), $tag_attributes = array(), $layout_path, $validated = false;

    public function __construct($_name, $_title, $_action = '', $_method = FormMethod::POST, $_use_token = true)
    {
        $this->error_message_formatter = array($this, 'defaultErrorFormatter');
        $this->name = $_name;
        $this->action = $_action;
        $this->method = $_method;
        $this->title = $_title;
        $this->use_token = $_use_token;
        $this->key = md5($this->name.'-'.$this->method);
        $this->token = md5($this->name.'-'.$this->method.'-'.uniqid().'-'.rand(10000,99999));
        $this->layout_path = ORION_LIB_DIR.'/form/layout/layout.default.php';
    }

    /**
     * Do form layout
     */
    public function getHtml()
    {
        ob_start();
        include($this->layout_path);
        $html = ob_get_contents();
        ob_end_clean();
        return $html;
    }

    /**
     * Defined which layout to use
     * @param $_name
     * @throws LayoutDoesNotExistException
     */
    public function setLayout($_name)
    {
        $path = Router::getInstance()->getModule()->getLayoutDirectory().'/layout.'.strtolower($_name).'.php';
        if( !FileSystem::checkFile($path) )
            throw(new LayoutDoesNotExistException("The following layout does not exist: ".$_name." (Path: ".$path.")"));
        $this->layout_path = $path;
    }

    /**
     * Add a new field
     * @param $text
     * @param $field
     * @param array $validators
     * @return mixed
     */
    public function addField($text, $field, $validators = array())
    {
        foreach($validators as $validator)
            $field->addValidator($validator);
        $this->fields[$field->getName()] = $field;
        $this->texts[$field->getName()] = $text;
        return end($this->fields);
    }

    /**
     * Set a form tag attribute value
     * @param $name
     * @param $value
     */
    public function setTagAttribute($name, $value)
    {
        $this->tag_attributes[$name] = $value;
    }

    /**
     * Fetch value from request or generates token
     * @throws WrongTokenException
     */
    public function dispatch()
    {
        if($this->getRequestValue($this->key) !== false)
        {
            if(!$this->use_token || $this->getRequestValue($this->key) === Session::getInstance()->get('Form/'.$this->getKey()))
            {
                foreach($this->fields as $field)
                {
                    $field->setValue($this->getRequestValue($field->getName()));
                }
                // -- Reset token if needed
                if($this->use_token)
                    $this->token = $this->getRequestValue($this->key);
                return true;
            }
            else
                throw(new WrongTokenException("The token received did not match the generated value."));
        }
        elseif($this->use_token)
        {
            // -- Generates token if needed:
            Session::getInstance()->set('Form/'.$this->getKey(), $this->token);
        }
        return false;
    }

    /**
     * Validate field values
     * @return bool
     */
    public function validate()
    {
        $this->validated = true;
        $this->errors = array();
        foreach($this->fields as $title => $field)
        {
            $element = $field->validate();
            if($element !== true)
                $this->errors[] = array('title' => $title, 'field' => $field, 'error' => $element);
        }
        return sizeof($this->errors) === 0;
    }

    /**
     * Returns true if no error have been tagged by validators
     * @return bool
     */
    public function isValid()
    {
        return sizeof($this->errors) === 0;
    }

    /**
     * Get validation errors
     * @throws FormNotValidatedException
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Get field value from request based on used method
     * @param $_key
     * @return bool
     */
    private function getRequestValue($_key)
    {
        return $this->method === FormMethod::POST ?
                (Request::getInstance()->isSetPost($_key) ? Request::getInstance()->getPost($_key) : false)
              : (Request::getInstance()->isSetGet($_key)  ? Request::getInstance()->getGet($_key)  : false);
    }

    /**
     * Get open tag html
     */
    public function openTag()
    {
        $attrs = '';
        $this->tag_attributes['name'] = $this->getName();
        $this->tag_attributes['method'] = $this->method;
        $this->tag_attributes['action'] = $this->getAction();
        foreach($this->tag_attributes as $key => $value)
        {
            $attrs .= $key.'="'.addslashes($value).'" ';
        }
        return '<form '.$attrs.'>';
    }

    /**
     * Format an error code into a readable message
     * @param $error
     * @return string
     */
    public function formatError($error)
    {
        return call_user_func($this->error_message_formatter, $error);
    }

    /**
     * Defines the error formatter
     * @param callable $function
     */
    public function setErrorFormatter(callable $function)
    {
        $this->error_message_formatter = $function;
    }

    /**
     * Set up a record creation form
     * @param $record
     * @param bool $enable_relation
     * @param array $exclusions
     * @param array $inclusions
     */
    public function fromObject($record, $enable_relation = false, $exclusions = array(), $inclusions = array())
    {
        $rows = $record->getTable()->getRows();
        $rels = $record->getTable()->getRelations();
        foreach($rows as $row)
        {
            if($row->getName() !== 'id' && (sizeof($inclusions) == 0 || in_array($inclusions, $row->getName())) && !in_array($exclusions, $row->getName()))
            {
                $a = $this->getFieldFromRow($row);
                $b = $this->getValidatorsFromRow($row);
                $this->addField(ucfirst($row->getName()), $a, $b);
            }
        }
        if($enable_relation)
        {
            foreach($rels as $rel)
            {
                if((sizeof($inclusions) == 0 || in_array($inclusions, $rel->getAlias())) && !in_array($exclusions, $rel->getAlias()))
                    $this->addField(ucfirst($rel->getAlias()), $this->getFieldFromRelation($rel), $this->getValidatorsFromRelation($rel, $record));
            }
        }
    }

    /**
     * Make a record persistent
     */
    public function save()
    {

    }

    /**
     * Returns a field based on row type
     * @param $row
     * @return \Field
     */
    public function getFieldFromRow($row)
    {
        switch($row->getType())
        {
            case RowType::ENUM:
            case RowType::SET:
                return new SelectField($this, $row->getName(), $row->getPossibleValues());
            case RowType::LONGTEXT:
            case RowType::MEDIUMTEXT:
            case RowType::TEXT:
            case RowType::TINYTEXT:
                return new TextAreaField($this, $row->getName());
        }
        return new TextField($this, $row->getName());
    }

    /**
     * Returns a field validator based on row type
     * @param $row
     * @return array
     */
    public function getValidatorsFromRow($row)
    {
        $result = array();
        switch($row->getType())
        {
            case RowType::BIGINT:
            case RowType::INTEGER:
            case RowType::INT:
            case RowType::MEDIUMINT:
            case RowType::SMALLINT:
            case RowType::TINYINT:
                $result[] = new IntegerValidator();
                break;
            case RowType::DECIMAL:
            case RowType::DOUBLE:
            case RowType::FLOAT:
            case RowType::NUMERIC:
            case RowType::REAL:
                $result[] = new NumericValidator();
                break;
        }
        if($row->getNull() !== true)
            $result[] = new RequiredValidator();
        return $result;
    }

    /**
     * Returns a field based on relation type
     * @param $rel
     * @return \Field
     */
    public function getFieldFromRelation($rel)
    {
        switch($rel->getType())
        {
            case RelationType::ManyToMany:
            case RelationType::OneToMany:
                return new MultiSelectField($this, $rel->getAlias(), $rel->getPossibleValues());

            case RelationType::ManyToOne:
            case RelationType::OneToOne:
            if(isset($this->fields[$rel->getField1()]))
                unset($this->fields[$rel->getField1()]);
                return new SelectField($this, $rel->getAlias(), $rel->getPossibleValues());
        }
        return new TextField($this, $rel->getName());
    }

    /**
     * Returns a field validator based on relation type
     * @param $rel
     * @param $row
     * @return array
     */
    public function getValidatorsFromRelation($rel, $row)
    {
        $result = array();
        switch($rel->getType())
        {
            case RelationType::ManyToMany:
            case RelationType::ManyToOne:
                break;
            case RelationType::OneToMany:
            case RelationType::OneToOne:
                if($row->getTable()->getRow($rel->getField1())->getNull() !== true)
                    $result[] = new RequiredValidator();
                break;
        }
        return $result;
    }

    /**
     * Default error formatter
     * @param $error
     * @return string
     */
    public function defaultErrorFormatter($error)
    {
        switch($error['error'])
        {
            case 'FIELD_REQUIRED':
                $msg = 'This field is required';
                break;
            case 'EMAIL_NOT_VALID':
                $msg = 'This is not a valid email address';
                break;
            case 'NOT_NUMERIC':
                $msg = 'This must be a number';
                break;
            case 'NOT_INTEGER':
                $msg = 'This must be an integer';
                break;
            case 'NOT_ALPHANUMERIC':
                $msg = 'Must be alpha-numeric only';
                break;
            case 'NOT_ALPHA':
                $msg = 'Must contains letters only';
                break;
            case 'NOT_A_VALID_STRING':
                $msg = 'This is not a valid string. Please only use alphanumeric characters and the underscore sign';
                break;
            default:
                $msg = 'Unknown error '.$error['error'];
        }
        return $error['title'].': '.$msg;
    }

    /**
     * Returns a given field
     * @param $key
     */
    final public function getField($key)
    {
        return $this->fields[$key];
    }

    /**
     * Returns a given field's text
     * @param $key
     */
    final public function getText($key)
    {
        return $this->texts[$key];
    }

    /**
     * Get token field's tag
     */
    public function tokenFieldTag()
    {
        return '<input type="hidden" name="'.$this->key.'" value="'.$this->token.'" />';
    }

    /**
     * Get close tag html
     * @return string
     */
    public function closeTag()
    {
        return '</form>';
    }

    /**
     * Return form's fields
     * @return array
     */
    public function getFields()
    {
       return $this->fields;
    }

    /**
     * Get form name
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get form title
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Get form method
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Get form action
     * @return mixed
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Get form key
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

}