<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Orm\Mysql;

/**
 * Query builder Adapter for Mysql database.
 *
 * Allows easy creation of mysql queries.
 *
 */
class Query 
{

    /**
     * Available aggregate functions for MySQL
     *
     * Used from {@link having()} method to validate if the aggregate 
     * function provided by user exists.
     *
     * @var array
     */
    private $_AGGREGATE = array(
        'AVG',
        'MAX',
        'MIN',
        'SUM',
        'COUNT',
        'BIT_AND',
        'BIT_OR',
        'BIT_XOR',
        'GROUP_CONCAT',
        'STD',
        'STDDEV_POP',
        'STDDEV_SAMP',
        'STDDEV',
        'VAR_POP',
        'VAR_SAMP',
        'VARIANCE',
    );

    protected $model;

    protected $select;
    
    protected $table;

    protected $where=array();

    protected $bind_params = array();
    
    protected $limit;
    
    protected $offset;
    
    protected $having;
    
    protected $group_by;
    
    protected $order_by;

    protected $aggregate=array();

    protected $query;

    protected $real_query;

    protected $join_conditions;

    protected $adapter;

    public function __construct($model=null, $adapter=null)
    {
        $this->model = $model;
        $this->table = $model ? $model::$table : null;
        $this->adapter = $adapter;
    }

    public function setQuery($query)
    {
        $this->query = $query;
    }

    public function toSql()
    {
        return $this->query ?: $this->build_sql();
    }

    public function getBindParams()
    {
        return $this->bind_params;
    }

    public function setModel($model)
    {
        $this->model = $model;
    }

    public function getModel()
    {
        return $this->model;
    }

    public function groupBy($args)
    {
        $this->group_by = $args;

        return $this;
    }

    public function orderBy($field, $order)
    {
        $this->order_by = null === $this->order_by 
            ? "{$field} ${order}" 
            : $this->order_by . ", {$field} ${order}";

        return $this;
    }

    public function __call($name, $args)
    {
        if (in_array(strtoupper($name), $this->_AGGREGATE)) {
            return $this->aggregate($name, $args[0], $args[1]);
        }
        throw new \Exception("Invalid method ".get_class($this)."::$name");
    }

    protected function aggregate($function, $field, $as = null)
    {
        $function = strtoupper($function);
        $this->aggregate[] = array(
            'function' => $function,
            'field'    => $field,
            'as'       => $as
        );

        return $this;
    }

    public function innerJoin($join_table, $primary_key, $foreign_key, $additional = null)
    {
        $this->_join("INNER", $join_table, $primary_key, $foreign_key, $additional);
        return $this;
    }

    public function leftJoin($join_table, $primary_key, $foreign_key, $additional = null)
    {
        $this->_join("LEFT", $join_table, $primary_key, $foreign_key, $additional);
        return $this;
    }

    private function _join($join_type, $join_table, $primary_key, $foreign_key, $additional = null)
    {
        list($pri_table, $pri_field) = explode('.', $primary_key);
        list($for_table, $for_field) = explode('.', $foreign_key);
        $this->join_conditions[] = "{$join_type} JOIN `{$join_table}` ON (`{$pri_table}`.{$pri_field} = `{$for_table}`.{$for_field} {$additional}) "; 
    }
    
    /**
     * Select fields from a table.
     * 
     * @param array|string $args the fields to select in array or comma(,)
     *                           seperated string
     */
    public function select($args="*")
    {
        $args = is_array($args) ? implode(',', $args) : $args;
        empty($this->select) 
            ? $this->select .= $args
            : $this->select .= ", " . $args;       
        
        return $this; 
    }

    public function from($table)
    {
        $this->table = $table;
        return $this;
    }

    public function getTable()
    {
        $model = $this->model;
        return $model
            ? $model::$table 
            : ($this->table ? $this->table : null);
    }


    /**
     * Create where conditions
     *
     * Example 1:
     * <code>
     * Object::find()->where(array('id = ? and username = ?', '1', 'John') )
     * </code>
     *
     * Example 2:
     * <code>
     * Object::find()->where(array('id'=>array('1','2'), 'name' =>'John') )
     * </code>
     *
     * @param array  $conditions 
     * @param string $operator   The operator for conditions, AND | OR
     */
    public function where(array $conditions, $operator='AND')
    {
        $array_keys = array_keys($conditions);
        if (reset($array_keys) === 0 &&
            end($array_keys) === count($conditions) - 1 &&
            !is_array(end($conditions))) 
        {
            $condition = array_shift($conditions);

            $this->where[] = array(
                'query' => $condition,
                'binds' => $conditions,
                'operator' => $operator
            );
        }  else {
            $model = $this->model;
            foreach ($conditions as $key => $value) {
                if (is_array($value)) {
                    $query = trim(str_repeat('?, ',count($value)), ', ');
                    $this->where[] = array(
                        'query' => "$key IN ( $query )",
                        'binds' => $value,
                        'operator' => $operator
                    );
                } else {
                    $this->where[] = array(
                        'query' => "$key = ?",
                        'binds' => array($value),
                        'operator' => $operator
                    );
                }
            }
        }
        
        return $this;       
    }

    public function andWhere(array $conditions)
    {
        return $this->where($conditions, 'AND');
    }

    public function orWhere(array $conditions)
    {
        return $this->where($conditions, 'OR');
    }

    public function whereIsNull($field, $operator='AND')
    {

        $this->where[] = array(
            'query' => "$field IS NULL",
            'binds' => array(),
            'operator' => $operator
        );
        return $this;
    }

    public function whereIsNotNull($field, $operator='AND')
    {

        $this->where[] = array(
            'query' => "$field IS NOT NULL",
            'binds' => array(),
            'operator' => $operator
        );
        return $this;
    }

    public function limit($count)
    {
        $this->limit = (int) $count;
        return $this;
    }

    public function offset($count)
    {
        $this->offset = (int) $count; 
        return $this;
    }

    public function having($aggregate_function, $column, $operator, $value)
    {
        if (!in_array($aggregate_function, $this->_AGGREGATE)) {
            throw new \InvalidArgumentException(
                "Invalid aggregate function {$aggregate_function}"
            );
        }
        
        $this->having = array(
            'query' => "{$aggregate_function}(?) $operator ?",
            'binds' => array(
                $column,
                $value
            )
        );

        return $this;
    }

    protected function build_where()
    {
        $query = "";
        foreach($this->where as $k=>$a) {
            $query .= ($k!=0 ? $a['operator'] ." " : null) . $a['query'] . " ";
            $this->bind_params = array_merge($this->bind_params, $a['binds']);
        }
        return trim($query);
    }

    protected function build_having()
    {
        
        $this->bind_params = array_merge($this->bind_params, $this->having['binds']);
        
        return $this->having['query'];
    }

    protected function build_select()
    {
        if ( null === $this->select ) {
            return "*";
        }
        $args = $this->select;
        
        if (!is_array($args))
            $args = array_unique(array_map('trim', explode(',', $args)));
        
        $this->select = implode(', ',$args);
        return $this->select;
    }

    public function build_sql()
    {
        $model = $this->model;

        if ( !empty($this->join_conditions) ) $this->select("`{$model::$table}`.*");
        
        $query = "SELECT {$this->build_select()}";

        // aggregates
        if (!empty($this->aggregate)) {
            foreach ($this->aggregate as $a) {
                $query .= ", "
                    .$a['function']
                    ."({$a['field']})"
                    .(isset($a['as']) ? ' as '.$a['as'] : null);
            }
        }

        $query .= " FROM {$this->getTable()}";
        if (!empty($this->join_conditions))
            $query .= " " . implode(" ", $this->join_conditions);
        if (!empty($this->where))
            $query .= " WHERE {$this->build_where()}";
        if (isset($this->group_by))
            $query .= " GROUP BY {$this->group_by}";
        if (isset($this->order_by))
            $query .= " ORDER BY {$this->order_by}";
        if (!empty($this->having))
            $query .= " HAVING {$this->build_having()}";
        if (isset($this->limit))
            $query .= " LIMIT {$this->offset},{$this->limit}";

        $this->query = $query;

        return $query;
    }
    
    public function paginate($per_page = 20)
    {
        $total_query = clone $this;
        $total = $total_query->count('*','total_count')->fetch()->total_count;
        $page = \Paginator::page($total, $per_page);
        
        $results = $this->forPage($page, $per_page)->fetchAll();

        return \Paginator::make($results, $total, $per_page);
    }

    public function forPage($page, $per_page)
    {
        return $this->offset(($page-1) * $per_page)->limit($per_page);
    }

    public function fetchAll()
    {
        return $this->_fetch('all');
    }

    public function fetch()
    {
        return $this->_fetch('one');
    }

    private function _fetch($mode)
    {
        $this->build_sql();
        
        $result = $this->adapter
            ->execute($this)
            ->get_result();

        $iterator = new ResultIterator($result);

        // if no model was assigned to query then return the ResultIterator 
        // instance
        if (!$this->model) {

            return $iterator;
        }
    
        $collection = new Collection($iterator, $this->model);

        if ('all' == $mode) {
            return $collection;    
        } elseif ('one' == $mode) {
            return $collection->first();
        }
    }

    /**
     * Compiles an insert query and executes it.
     * 
     * @param string $table  the table in database to insert into.
     * @param array  $params an array with keys as fields of table and values 
     *                       as the values ti insert into.
     */
    public function insert($table, $params)
    {
        $keys = implode(', ', array_keys($params));
        $values = trim(str_repeat('?, ',count($params)), ', ');
        $this->query = "INSERT INTO  `{$table}` ({$keys}) VALUES ({$values})";
       
        $this->bind_params = $params;

        return $this->adapter
            ->execute($this, 'Create')
            ->insert_id;
    }

    /**
     * Compiles an update query and executes it.
     */
    public function update($table, $params, $where=array())
    {
        $data = "";
        foreach ($params as $name=>$value) {
            $data .= $name . " = ?, "; 
        }
        $data = rtrim($data, ", ");
        
        $this->bind_params = $params;

        if (!empty($where)) {
            $this->where($where); 
        }
 
        $where = $this->build_where();

        $this->query = "UPDATE `{$table}` SET {$data} WHERE {$where}";
        
        return $this->adapter
            ->execute($this, 'Update')
            ->affected_rows;
    }

    public function delete($model_or_table, $where=null)
    {

        if ($model_or_table instanceof \AbstractModel) {
            $class = get_class($model_or_table);
            $pk    = $class::$primary_key; 
            $where = $where ?: "$pk = {$model_or_table->$pk}";
            $table = $class::$table;
        } else {
            $table = $model_or_table; 
        } 

        if (!empty($where)) {
            $this->where($where); 
        } else {
            $pk = $class::$primary_key;
            $this->where(
                array(
                    $pk => $model->$pk
                )
            );
        }
       
        $where = $this->build_where();

        $this->query = "DELETE FROM `{$table}` WHERE {$where}";

        return $this->execute()
            ->affected_rows;
    }

    public function __toString()
    {
        return $this->toSql();
    }
}
