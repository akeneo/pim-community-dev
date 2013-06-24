<?php

namespace Oro\Bundle\FormBundle\Config;

class SubBlockConfig implements FormConfigInterface
{
    /**
     * @var string
     */
    protected $title;

    /**
     * @var array
     */
    protected $data = array();

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
            'title' => $this->title,
            'data'  => $this->data
        );
    }
}
