<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Validator\Db;

use Zend\Validator\Exception;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\TableIdentifier;

/**
 * Check whether the given field Exceeds the maximum value.
 */
class ExceedsMax extends AbstractDb
{

    /**
     * Error constants
     */
    const ERROR_RECORD_EXCEEDS_MAX_VALUE    = 'exceedsmax';

    /**
     * @var array Message templates
     */
    protected $messageTemplates = array(
        self::ERROR_RECORD_EXCEEDS_MAX_VALUE => "Record exceeds Maximum Value",
    
    );

    /**
     * Maximum value for the field
     *
     * @var string
     */
    protected $max ='';

    /**
     * Key column to search the record. Usually the primary key
     *
     * @var string
     */
    protected $key ='';

    /**
     * Value of the key column
     *
     * @var string
     */
    protected $key_value ='';



    public function __construct($options = null)
    {
        parent::__construct($options);

        /*
         * Validate the input and set the appropriate fields
         */
        if (!array_key_exists('max', $options)) {
            throw new Exception\InvalidArgumentException('Max Number option missing!');
        }
        else {
            $this->setMax($options['max']);
        }
        if (!array_key_exists('key', $options)) {
            throw new Exception\InvalidArgumentException('Key Field option missing!');
        }
        else {
            $this->setKey($options['key']);
        }

        if (!array_key_exists('key_value', $options)) {
            throw new Exception\InvalidArgumentException('Value for Key Field is missing!');
        }
        else {
            $this->setKeyValue($options['key_value']);
        }

        
        

    }

    /**
     * Sets a new field
     *
     * @param string $max
     * @return AbstractDb
     */
    public function setMax($max)
    {
        $this->max = (int) $max;
        return $this;
    }

    /**
     * Sets a new field
     *
     * @param string $key
     * @return AbstractDb
     */
    public function setKey($key)
    {
        $this->key = $key;
        return $this;
    }

    /**
     * Sets a new field
     *
     * @param string $key_value
     * @return AbstractDb
     */
    public function setKeyValue($key_value)
    {
        $this->key_value =  $key_value;
        return $this;
    }


    /**
     * Gets the select object to be used by the validator.
     *
     * @return Select The Select object which will be used
     */
    public function getSelect()
    {
        if ($this->select instanceof Select) {
            return $this->select;
        }

        // Build select object
        $select          = new Select();
        $tableIdentifier = new TableIdentifier($this->table, $this->schema);
        $select->from($tableIdentifier)->columns(array($this->field));
        $select->where->equalTo($this->key, $this->key_value);

        $this->select = $select;

        return $this->select;
    }

    /**
     * Run query and returns matches, or null if no matches are found.
     *
     * @param  string $value
     * @return array when matches are found.
     */
    protected function query()
    {
        $sql = new Sql($this->getAdapter());
        $select = $this->getSelect();
        $statement = $sql->prepareStatementForSqlObject($select);
        $result = $statement->execute();

        return $result->current();
    }


    public function isValid($value)
    {
        /*
         * Check for an adapter being defined. If not, throw an exception.
         */
        if (null === $this->adapter) {
            throw new Exception\RuntimeException('No database adapter present');
        }

        $valid = true;

        $result = $this->query();
        if (!$result) {
            $valid = false;
            $this->error(self::ERROR_NO_RECORD_FOUND);
        }
        elseif (($value + $result[$this->field]) > $this->max) {
            $valid = false;
            $this->error(self::ERROR_RECORD_EXCEEDS_MAX_VALUE);
        }

        return $valid;
    }
}
