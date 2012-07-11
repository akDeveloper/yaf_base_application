<?php 
namespace Orm;

abstract class Entity extends \Lycan\Validations\Validate
{
    public static $columns=array();
    public static $table;
    public static $primary_key = "id";

    private $_new_record = true;
    private $_to_save = array();
    private $_old = array();
    private $_attributes = array();

    protected static $adapter;

    protected $protected_attributes = array();


    public static function setAdapter(\Orm\Interfaces\Adapter $adapter)
    {
        static::$adapter = $adapter;
    }

    public static function getAdapter()
    {
        if (!(static::$adapter instanceof \Orm\Interfaces\Adapter)) {
            throw new \DomainException("Adapter must be an instance of \Orm\Interfaces\Adapter");
        }
        return static::$adapter;
    }

    public function __construct($attrs=array(), $new_record=true)
    {
        $this->_new_record = $new_record;
        $this->_set_attributes($attrs);
    }

    public function isNewRecord()
    {
        return $this->_new_record;
    }

    public function __get($name)
    {
        return array_key_exists($name, $this->_attributes) 
            ? $this->_attributes[$name]
            : null; 
    }

    public function getAttributes()
    {
        return $this->_attributes;
    }

    public function __set($name, $value)
    {
        if (array_key_exists($name, $this->_attributes))
            $this->_set_attribute($name, $value);
        else 
            $this->$name = $value; 
    }

    private function _set_attribute($attr, $value)
    {
        if ($this->_attributes[$attr] != $value) {
            $this->_to_save[$attr] = $value;
            $this->_old[$attr] = $this->_attributes[$attr];
            $this->_attributes[$attr] = $value;
        }
    }

    private function _set_attributes($attrs)
    {
        if ($this->_new_record) { 
            $this->_attributes = array_combine(static::$columns, 
                array_pad(array(), count(static::$columns), null));
        }

        foreach ($attrs as $k=>$v) {
            
            if (in_array($k,$this->protected_attributes)) continue;

            if (($this->_new_record 
                && !isset($this->_attributes[$k]))
                || (array_key_exists($k,$this->_attributes)
                && $this->_attributes[$k] != $v)
            ){
                $this->_to_save[$k] = $v;
                $this->_old[$k] = isset($this->_attributes[$k]) 
                    ? $this->_attributes[$k] 
                    : null;
            }
            if (in_array($k, static::$columns) ) {
                $this->_attributes[$k] = $v;
            } else {
                $this->$k = $v; 
            }

        }
    }

    private function _assign_attributes()
    {
        $attr = array();
        foreach (static::$columns as $column) {

            if ($column == static::$primary_key) continue;

            if (array_key_exists($column, $this->_to_save)) {
                $attr[$column] = $this->_to_save[$column];
            }
        }

        if (in_array('created_at', static::$columns) && $this->_new_record) {
            $attr['created_at'] = date('Y-m-d H:i:s',time());
        }

        if (in_array('updated_at', static::$columns)) {
            $attr['updated_at'] = date('Y-m-d H:i:s',time());
        }

        return $attr;
    }

    public static function find()
    {
        return static::getAdapter()->createQuery(get_called_class());
    }

    public static function findById($id)
    {
        return static::find()
            ->where(array('id'=>$id)); 
    }

    private function _reload()
    {
        $this->_to_save = array();
        $this->_old = array();
    }

    public function destroy()
    {
        return $this->getAdapter()->delete($this);
    }

    public function save($validate=true)
    {
        #if (empty($this->_to_save)) return true;
        $v = $validate ? $this->isValid() : true;
        return $v && $this->_create_or_update();
    }

    private function _create_or_update()
    {
        $result = $this->_new_record 
            ? $this->_create() 
            : $this->_update();
        
        return $result != false;
    }
    
    private function _create()
    {
        $attrs = $this->_assign_attributes();
        
        $id = self::find()->insert(static::$table, $attrs);
        if ($id) {
            $this->{static::$primary_key} = $id;
            $this->_new_record = false;
            $this->_reload();
            return true;
        } else {
            return false;
        } 
    }

    private function _update()
    {
        $attrs = $this->_assign_attributes();
        
        $pk = static::$primary_key;

        $update = self::find()
            ->where(array($pk=>$this->$pk))
            ->update(static::$table, $attrs);

        if ($update) {
            $this->_reload();
        }
        return $update;
    }

    public function updateAttributes($attr)
    {
        $this->_set_attributes($attr);
        return $this->save();
    }

    private function _get_public_attributes()
    {
        $attr = array();
        $ref = new ReflectionObject($this);
        $props = $ref->getProperties(ReflectionProperty::IS_PUBLIC);
        foreach ($props as $pro) {
            false && $pro = new ReflectionProperty();
            $attr[$pro->getName()] = $pro->getValue($this);
        }
        return $attr;
    }

    protected function validations()
    {

    }
}
