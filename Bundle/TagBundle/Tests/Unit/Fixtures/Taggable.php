<?php

namespace Oro\Bundle\TagBundle\Tests\Unit\Fixtures;

use Doctrine\Common\Collections\ArrayCollection;

use Oro\Bundle\TagBundle\Entity\Taggable as OROTag;

class Taggable implements OROTag
{
    protected $tags;

    protected $data;

    public function __construct($data = array())
    {
        $this->tags = new ArrayCollection();

        if (!isset($data['id'])) {
            $data['id'] = time();
        }

        $this->data = $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * {@inheritdoc}
     */
    public function setTags($tags)
    {
        $this->tags = $tags;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTaggableId()
    {
        return $this->data['id'];
    }

    /**
     * @param $name
     * @return null
     */
    public function __get($name)
    {
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }

        $trace = debug_backtrace();
        trigger_error(
            'Undefined property via __get(): ' . $name .
            ' in ' . $trace[0]['file'] .
            ' on line ' . $trace[0]['line'],
            E_USER_NOTICE
        );
        return null;
    }
}
