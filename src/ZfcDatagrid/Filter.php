<?php
namespace ZfcDatagrid;

use ZfcDatagrid\Column;

class Filter
{
    // OK
    const LIKE = '~ *%s*';
    
    // OK
    const LIKE_LEFT = '~ *%s';
    
    // OK
    const LIKE_RIGHT = '~ %s*';
    
    // OK
    const NOT_LIKE = '!~ *%s*';
    
    // OK
    const NOT_LIKE_LEFT = '!~ *%s';
    
    // OK
    const NOT_LIKE_RIGHT = '!~ %s*';
    
    // OK
    const EQUAL = '= %s';
    
    // OK
    const NOT_EQUAL = '!= %s';
    
    // OK
    const GREATER_EQUAL = '>= %s';
    
    // OK
    const GREATER = '> %s';
    
    // OK
    const LESS_EQUAL = '<= %s';
    
    // OK
    const LESS = '< %s';
    
    // OK
    const IN = '=(%s)';
    
    // OK
    const NOT_IN = '!=(%s)';

    const BETWEEN = '%s <> %s';

    /**
     *
     * @var Column\AbstractColumn
     */
    private $column;

    private $operator = self::LIKE;

    private $value;

    private $displayColumnValue;

    /**
     * Apply a filter based on a column
     *
     * @param Column\AbstractColumn $column            
     * @param unknown $inputFilterValue            
     */
    public function setFromColumn(Column\AbstractColumn $column, $inputFilterValue)
    {
        $this->column = $column;
        $this->setColumnOperator($inputFilterValue, $column->getFilterDefaultOperation());
    }

    /**
     * Convert the input filter to operator + filter + display filter value
     *
     * Partly idea taken from ZfDatagrid
     *
     * @see https://github.com/zfdatagrid/grid/blob/master/library/Bvb/Grid.php#L1438
     *
     * @param string $inputFilterValue            
     * @param mixed $defaultOperator            
     * @return array
     */
    private function setColumnOperator($inputFilterValue, $defaultOperator = self::LIKE)
    {
        $inputFilterValue = (string) $inputFilterValue;
        $inputFilterValue = trim($inputFilterValue);
        
        $operator = $defaultOperator;
        $value = $inputFilterValue;
        
        if (substr($inputFilterValue, 0, 2) == '=(') {
            $operator = self::IN;
            $value = substr($inputFilterValue, 2);
            if (substr($value, - 1) == ')') {
                $value = substr($value, 0, - 1);
            }
        } elseif (substr($inputFilterValue, 0, 3) == '!=(') {
            $operator = self::NOT_IN;
            $value = substr($inputFilterValue, 3);
            if (substr($value, - 1) == ')') {
                $value = substr($value, 0, - 1);
            }
        } elseif (substr($inputFilterValue, 0, 2) == '!=' || substr($inputFilterValue, 0, 2) == '<>') {
            $operator = self::NOT_EQUAL;
            $value = substr($inputFilterValue, 2);
        } elseif (substr($inputFilterValue, 0, 2) == '!~' || substr($inputFilterValue, 0, 1) == '!') {
            // NOT LIKE or NOT EQUAL
            if (substr($inputFilterValue, 0, 2) == '!~') {
                $value = trim(substr($inputFilterValue, 2));
            } else {
                $value = trim(substr($inputFilterValue, 1));
            }
            
            if (substr($inputFilterValue, 0, 2) == '!~' || (substr($value, 0, 1) == '%' || substr($value, - 1) == '%' || substr($value, 0, 1) == '*' || substr($value, - 1) == '*')) {
                // NOT LIKE
                if ((substr($value, 0, 1) == '*' && substr($value, - 1) == '*') || (substr($value, 0, 1) == '%' && substr($value, - 1) == '%')) {
                    $operator = self::NOT_LIKE;
                    $value = substr($value, 1);
                    $value = substr($value, 0, - 1);
                } elseif (substr($value, 0, 1) == '*' || substr($value, 0, 1) == '%') {
                    $operator = self::NOT_LIKE_LEFT;
                    $value = substr($value, 1);
                } elseif (substr($value, - 1) == '*' || substr($value, - 1) == '%') {
                    $operator = self::NOT_LIKE_RIGHT;
                    $value = substr($value, 0, - 1);
                } else {
                    $operator = self::NOT_LIKE;
                }
            } else {
                // NOT EQUAL
                $operator = self::NOT_EQUAL;
            }
        } elseif (substr($inputFilterValue, 0, 1) == '~' || substr($inputFilterValue, 0, 1) == '%' || substr($inputFilterValue, - 1) == '%' || substr($inputFilterValue, 0, 1) == '*' || substr($inputFilterValue, - 1) == '*') {
            // LIKE
            if (substr($inputFilterValue, 0, 1) == '~') {
                $value = substr($inputFilterValue, 1);
            }
            $value = trim($value);
            
            if ((substr($value, 0, 1) == '*' && substr($value, - 1) == '*') || (substr($value, 0, 1) == '%' && substr($value, - 1) == '%')) {
                $operator = self::LIKE;
                $value = substr($value, 1);
                $value = substr($value, 0, - 1);
            } elseif (substr($value, 0, 1) == '*' || substr($value, 0, 1) == '%') {
                $operator = self::LIKE_LEFT;
                $value = substr($value, 1);
            } elseif (substr($value, - 1) == '*' || substr($value, - 1) == '%') {
                $operator = self::LIKE_RIGHT;
                $value = substr($value, 0, - 1);
            } else {
                $operator = self::LIKE;
            }
        } elseif (substr($inputFilterValue, 0, 2) == '==') {
            $operator = self::EQUAL;
            $value = substr($inputFilterValue, 2);
        } elseif (substr($inputFilterValue, 0, 1) == '=') {
            $operator = self::EQUAL;
            $value = substr($inputFilterValue, 1);
        } elseif (substr($inputFilterValue, 0, 2) == '>=') {
            $operator = self::GREATER_EQUAL;
            $value = substr($inputFilterValue, 2);
        } elseif (substr($inputFilterValue, 0, 1) == '>') {
            $operator = self::GREATER;
            $value = substr($inputFilterValue, 1);
        } elseif (substr($inputFilterValue, 0, 2) == '<=') {
            $operator = self::LESS_EQUAL;
            $value = substr($inputFilterValue, 2);
        } elseif (substr($inputFilterValue, 0, 1) == '<') {
            $operator = self::LESS;
            $value = substr($inputFilterValue, 1);
        } elseif (strpos($inputFilterValue, '<>') !== false) {
            $operator = self::BETWEEN;
            $value = explode('<>', $inputFilterValue);
        }
        
        if ($value === false) {
            // NO VALUE applied...maybe only "="
            $value = '';
        }
        
        /*
         * Handle multiple values
         */
        if (is_string($value)) {
            $value = explode(',', $value);
        }
        foreach ($value as &$val) {
            $val = trim($val);
        }
        $this->operator = $operator;
        
        if ($operator == self::BETWEEN) {
            $value = array(
                min($value),
                max($value)
            );
            $this->displayColumnValue = sprintf($operator, $value[0], $value[1]);
        } else {
            $this->displayColumnValue = sprintf($operator, implode(',', $value));
        }
        
        /*
         * The searched value must be converted maybe.... - Translation - Replace - DateTime - ...
         */
        foreach ($value as &$val) {
            $type = $this->getColumn()->getType();
            $val = $type->getFilterValue($val);
            
            // @TODO Translation + Replace
        }
        
        $this->value = $value;
    }

    /**
     * 
     * @return boolean
     */
    public function isColumnFilter()
    {
        if ($this->getColumn() instanceof Column\AbstractColumn) {
            return true;
        } else {
            return false;
        }
    }

    /**
     *
     * @return \ZfcDatagrid\Column\AbstractColumn
     */
    public function getColumn()
    {
        return $this->column;
    }

    /**
     *
     * @return array
     */
    public function getValues()
    {
        return $this->value;
    }

    /**
     * 
     * @return string
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * Get the value displayed to the user
     *
     * @return string
     */
    public function getDisplayColumnValue()
    {
        return $this->displayColumnValue;
    }
}
