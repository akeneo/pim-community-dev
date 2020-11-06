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
     */
    public function getId(): int;

    /**
     * Set message
     *
     * @param string $message
     */
    public function setMessage(string $message): \Akeneo\Platform\Bundle\NotificationBundle\Entity\NotificationInterface;

    /**
     * Get message
     */
    public function getMessage(): string;

    /**
     * Set comment
     *
     * @param string $comment
     */
    public function setComment(string $comment): \Akeneo\Platform\Bundle\NotificationBundle\Entity\NotificationInterface;

    /**
     * Get comment
     */
    public function getComment(): string;

    /**
     * Set type
     *
     * @param string $type
     */
    public function setType(string $type): \Akeneo\Platform\Bundle\NotificationBundle\Entity\NotificationInterface;

    /**
     * Get type
     */
    public function getType(): string;

    /**
     * Set route
     *
     * @param string $route
     */
    public function setRoute(string $route): \Akeneo\Platform\Bundle\NotificationBundle\Entity\NotificationInterface;

    /**
     * Get route
     */
    public function getRoute(): string;

    /**
     * Set routeParams
     *
     * @param array $routeParams
     */
    public function setRouteParams(array $routeParams): \Akeneo\Platform\Bundle\NotificationBundle\Entity\NotificationInterface;

    /**
     * Get routeParams
     */
    public function getRouteParams(): array;

    /**
     * Set messageParams
     *
     * @param array $messageParams
     */
    public function setMessageParams(array $messageParams): \Akeneo\Platform\Bundle\NotificationBundle\Entity\NotificationInterface;

    /**
     * Get messageParams
     */
    public function getMessageParams(): array;

    /**
     * Get created
     */
    public function getCreated(): \DateTime;

    /**
     * Set context
     *
     * @param array $context
     */
    public function setContext(array $context): \Akeneo\Platform\Bundle\NotificationBundle\Entity\NotificationInterface;

    /**
     * Get context
     */
    public function getContext(): array;
}
