<?php

namespace Pim\Bundle\NotificationBundle\Entity;

/**
 * Notification entity
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Notification
{
    /** @var int */
    protected $id;

    /** @var string */
    protected $route;

    /** @var array */
    protected $routeParams = [];

    /** @var string */
    protected $message;

    /** @var array */
    protected $messageParams = [];

    /** @var string */
    protected $comment;

    /** @var \DateTime */
    protected $created;

    /** @var string */
    protected $type;

    /** @var array */
    protected $context = [];

    public function __construct()
    {
        $this->created = new \DateTime('now');
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $message
     *
     * @return Notification
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $comment
     *
     * @return Notification
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param string $type
     *
     * @return Notification
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $route
     *
     * @return Notification
     */
    public function setRoute($route)
    {
        $this->route = $route;

        return $this;
    }

    /**
     * @return string
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * @param array $routeParams
     *
     * @return Notification
     */
    public function setRouteParams(array $routeParams)
    {
        $this->routeParams = $routeParams;

        return $this;
    }

    /**
     * @return array
     */
    public function getRouteParams()
    {
        return $this->routeParams;
    }

    /**
     * @param array $messageParams
     *
     * @return Notification
     */
    public function setMessageParams(array $messageParams)
    {
        $this->messageParams = $messageParams;

        return $this;
    }

    /**
     * @return array
     */
    public function getMessageParams()
    {
        return $this->messageParams;
    }

    /**
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set the context (['actionType' => 'export'] for example)
     *
     * @param array $context
     *
     * @return Notification
     */
    public function setContext(array $context)
    {
        $this->context = $context;

        return $this;
    }

    /**
     * Get the context (['actionType' => 'export'] for example)
     *
     * @return array
     */
    public function getContext()
    {
        return $this->context;
    }
}
