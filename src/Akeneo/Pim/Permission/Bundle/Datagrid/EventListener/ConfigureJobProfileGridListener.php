<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Bundle\Datagrid\EventListener;

use Akeneo\Pim\Permission\Component\Attributes;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\DataGridBundle\Extension\Action\ActionExtension;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Grid listener to configure job profile grid row actions
 *
 * @author Filips Alpe <filips@akeneo.com>
 */
class ConfigureJobProfileGridListener
{
    /** @var AuthorizationCheckerInterface $authorizationChecker */
    protected $authorizationChecker;

    /**
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
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
        return !$this->authorizationChecker->isGranted(Attributes::EDIT, $record->getRootEntity())
            ? ['edit' => false]
            : null
        ;
    }
}
