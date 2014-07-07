<?php

namespace PimEnterprise\Bundle\DataGridBundle\EventListener;

use Symfony\Component\Security\Core\SecurityContextInterface;
use Oro\Bundle\DataGridBundle\Event\BuildBefore;
use Oro\Bundle\DataGridBundle\Extension\Action\ActionExtension;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use PimEnterprise\Bundle\SecurityBundle\Voter\JobProfileVoter;

/**
 * Grid listener to configure job profile grid row actions
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
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
        if (!$this->securityContext->isGranted(JobProfileVoter::EDIT_JOB_PROFILE, $record->getRootEntity())) {
            return ['edit' => false, 'delete' => false];
        }
    }
}
