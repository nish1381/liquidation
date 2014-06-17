<?php

class DL_Product_Option {

    private $id;
    private $order;
    private $name;
    private $value;
    private $field;

    function __construct($id, $name, $order, $value, $field = null)
    {
        $this->id = $id;
        $this->name = $name;
        $this->order = $order;
        $this->value = $value;
        $this->field = $field;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $order
     */
    public function setOrder($order)
    {
        $this->order = $order;
    }

    /**
     * @return mixed
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param null $field
     */
    public function setField($field)
    {
        $this->field = $field;
    }

    /**
     * @return null
     */
    public function getField()
    {
        return $this->field;
    }

}
 