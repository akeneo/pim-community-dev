<?php

namespace Oro\Bundle\FormBundle\Config;

class SubBlockConfig implements FormConfigInterface
{
    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $code;

    /**
     * @var int
     */
    protected $priority;

    /**
     * @var array
     */
    protected $data = array();

    public function __construct($code)
    {
        $this->code = $code;
    }

    /**
     * @param $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param int $priority
     * @return $this
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;

        return $this;
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param $data
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param $data
     * @return $this
     */
    public function addData($data)
    {
        $this->data[] = $data;

        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return array(
            'code'  => $this->code,
            'title' => $this->title,
            'data'  => $this->data
        );
    }
}
