<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\CatalogRuleBundle\Factory;

use Pim\Bundle\NotificationBundle\Factory\NotificationFactoryInterface;

/**
 * @author Damien Carcel <damien.carcel@gmail.com>
 */
class RuleExecutionNotificationFactory implements NotificationFactoryInterface
{
    const SUPPORTED_TYPE = 'rule';

    /** @var string */
    protected $notificationClass;

    /**
     * @param string $notificationClass
     */
    public function __construct($notificationClass)
    {
        $this->notificationClass = $notificationClass;
    }

    /**
     * {@inheritdoc}
     */
    public function create($count)
    {
        $notification = new $this->notificationClass();

        $notification
            ->setType('success')
            ->setMessage(sprintf(
                'pimee_catalog_rule.notification.%s.%s',
                static::SUPPORTED_TYPE,
                'executed'
            ))
            ->setMessageParams(['%count%' => $count])
            ->setRoute('pimee_catalog_rule_rule_index')
            ->setContext([
                'actionType'       => static::SUPPORTED_TYPE,
                'showReportButton' => false,
            ]);

        return $notification;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($type)
    {
        return $type === static::SUPPORTED_TYPE;
    }
}
