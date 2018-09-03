<?php

namespace Akeneo\Platform\Bundle\NotificationBundle\Entity;

/**
 * Notification interface
 *
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface NotificationInterface
{
    /**
     * Get id
     *
     * @return int
     */
    public function getId();

    /**
     * Set message
     *
     * @param string $message
     *
     * @return NotificationInterface
     */
    public function setMessage($message);

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage();

    /**
     * Set comment
     *
     * @param string $comment
     *
     * @return NotificationInterface
     */
    public function setComment($comment);

    /**
     * Get comment
     *
     * @return string
     */
    public function getComment();

    /**
     * Set type
     *
     * @param string $type
     *
     * @return NotificationInterface
     */
    public function setType($type);

    /**
     * Get type
     *
     * @return string
     */
    public function getType();

    /**
     * Set route
     *
     * @param string $route
     *
     * @return NotificationInterface
     */
    public function setRoute($route);

    /**
     * Get route
     *
     * @return string
     */
    public function getRoute();

    /**
     * Set routeParams
     *
     * @param array $routeParams
     *
     * @return NotificationInterface
     */
    public function setRouteParams(array $routeParams);

    /**
     * Get routeParams
     *
     * @return array
     */
    public function getRouteParams();

    /**
     * Set messageParams
     *
     * @param array $messageParams
     *
     * @return NotificationInterface
     */
    public function setMessageParams(array $messageParams);

    /**
     * Get messageParams
     *
     * @return array
     */
    public function getMessageParams();

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated();

    /**
     * Set context
     *
     * @param array $context
     *
     * @return NotificationInterface
     */
    public function setContext(array $context);

    /**
     * Get context
     *
     * @return array
     */
    public function getContext();
}
