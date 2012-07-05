<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

namespace Orm\Mysql;

use Orm\Mysql\ResultItearator;
use Orm\Mysql\Collection;
/**
 * Adapter for Mysql database.
 *
 * Using Mysli extension to connect to a MySql database.
 *
 */
class Adapter implements \Orm\Interfaces\Adapter
{

    /**
     * An array contain configuration data for this adapter
     * 
     * Possible values are
     *
     * host     the host name or an IP address of MySQL server
     * port     Specifies the port number to attempt to connect to the MySQL 
     *          server.
     * database the database name to connect.
     * user     the user that has access to this database
     * password password for this user
     * charset  the default client character set
     *
     * @var array
     */
    protected $config;

    /**
     * The mysqli connection
     *
     * @var \mysqli
     */
    protected $connection;


    public function __construct($config) 
    {
        $this->config = is_array($config) ? $config : $config->toArray();
    }

    /**
     * Return the current connection between PHP and Mysql database.
     *
     * @return \mysli
     */
    public function connection()
    {
        $this->connect();

        return $this->connection;
    }

    /**
     * Creates a connection between PHP and Mysql Orm
     *
     * @throws \Exception
     *
     * @return void
     */
    public function connect()
    {
        if ( null === $this->connection ) {

            extract($this->config);
            
            $this->connection = new \mysqli($host, $user, $password, $database);

            if ($this->connection->connect_error)
                throw new \Exception("Could not connect to database server! [" . $this->connection->connect_error . "]");
            
            if (!empty($charset)) {
                $this->connection->set_charset($charset);
            }
        }
    }
    
    /**
     * Close th connection to database and unset the connection property.
     *
     * @return void
     */
    public function disconnect()
    {
        $this->connection->close();
        $this->connection = null;
    }

    /**
     * Prepares and executes a query and return the statement
     * 
     * if $params is null this variable will be filled by {@link $bind_params} 
     * property
     * 
     * @param array  $params optional an array with values to bind to the prepared 
     *                       statement. 
     * @param string $action optional the action that represent this query.
     *                       Create for INSERT, Update for UPDATE, Load for SELECT,
     *                       Delete for DELETE.
     *
     * @return mysqli_stmt 
     */
    public function execute($query, $action='Load') 
    {
        $params = $query->getBindParams();
        
        $stmt = $this->prepare($query->toSql());
        $this->bind_params($stmt, $params);
        
        $this->real_query = $query->toSql();
        foreach ($params as $value) {
            $this->sanitize($value);
            $this->real_query = preg_replace('|\?|', $value, $this->real_query, 1);
        }
        
        $start = microtime(true);
        
        $stmt->execute();
        
        \Logger::getLogger()->logQuery($this->real_query, $query->getModel(), (microtime(true) - $start), $action);
        
        
        if ( 0 !== $stmt->errno ) {
            throw new \Exception($this->getStmt()->error);
        }

        return $stmt;
    }

    protected function prepare($query=null)
    { 
        $query = $query ?: $this->query;

        $stmt = $this->connection()->prepare($query);
        
        \Logger::getLogger()->log("Prepare: $query");

        if (false == $stmt ) {
            throw new \Exception(
                 $this->connection()->error . "\n"
                 . "[{$this->connection()->errno}] "
                . $query . "//"
            );
        }

        return $stmt;
    }

    protected function bind_params($stmt, $params)
    {
        if (!empty($params)) {
            $types = "";
            $ref = array();

            foreach($params as $key=>$value) {
                $types .= is_float($value) ? 'd' : (is_int($value) ? 'i' : (is_string($value) ? 's' : 'b'));
                $ref[$key] = &$params[$key]; 
            }
            array_unshift($params, $types);
            $method = new \ReflectionMethod($stmt, 'bind_param');
            $method->invokeArgs($stmt, $params);
        }
    }

    public function sanitize(&$value)
    {
        if (null === $value) {
            $value = 'NULL';
            return;
        }

        $value = $this->connection()->real_escape_string($value);
        $value = is_numeric($value) ? $value : $this->quote($value);
    }

    public function quote($string)
    {
        if ( substr_count($string, "'") == 2 ) return $string;

        return "'" . $string . "'";       
    }

    public function createQuery($model=null)
    {
        return new Query($model, $this);
    }
}
