<?php

namespace Akeneo\Platform\Bundle\NotificationBundle\Entity;

/**
 * Notification entity
 *
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Notification implements NotificationInterface
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
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * {@inheritdoc}
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * {@inheritdoc}
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * {@inheritdoc}
     */
    public function setRoute($route)
    {
        $this->route = $route;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * {@inheritdoc}
     */
    public function setRouteParams(array $routeParams)
    {
        $this->routeParams = $routeParams;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteParams()
    {
        return $this->routeParams;
    }

    /**
     * {@inheritdoc}
     */
    public function setMessageParams(array $messageParams)
    {
        $this->messageParams = $messageParams;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getMessageParams()
    {
        return $this->messageParams;
    }

    /**
     * {@inheritdoc}
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * {@inheritdoc}
     */
    public function setContext(array $context)
    {
        $this->context = $context;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getContext()
    {
        return $this->context;
    }
}
