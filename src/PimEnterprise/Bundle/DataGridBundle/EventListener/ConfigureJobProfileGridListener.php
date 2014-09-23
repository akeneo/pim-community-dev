<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\DataGridBundle\EventListener;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\DataGridBundle\Extension\Action\ActionExtension;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * Grid listener to configure job profile grid row actions
 *
 * @author Filips Alpe <filips@akeneo.com>
 */
class ConfigureJobProfileGridListener
{
    /** @var SecurityContextInterface $securityContext */
    protected $securityContext;

    /**
     * @param SecurityContextInterface $securityContext
     */
    public function __construct(SecurityContextInterface $securityContext)
    {
        $this->securityContext = $securityContext;
    }

    /**
     * {@inheritdoc}
     */
    public function onBuildBefore(BuildBefore $event)
    {
        $configuration = $event->getConfig();

        $configuration->offsetSetByPath(
            sprintf('[%s]', ActionExtension::ACTION_CONFIGURATION_KEY),
            function (ResultRecordInterface $record) {
                return $this->getActionConfiguration($record);
            }
        );
    }

    /**
     * @param ResultRecordInterface $record
     *
     * @return array|null
     */
    protected function getActionConfiguration(ResultRecordInterface $record)
    {
        if (!$this->securityContext->isGranted(Attributes::EDIT, $record->getRootEntity())) {
            return ['edit' => false, 'delete' => false];
        }
    }
}
