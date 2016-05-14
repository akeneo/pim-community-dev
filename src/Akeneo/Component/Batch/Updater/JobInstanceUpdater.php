<?php

namespace Akeneo\Component\Batch\Updater;

use Akeneo\Bundle\BatchBundle\Connector\ConnectorRegistry;
use Akeneo\Component\Batch\Job\Job;
use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Job\JobParametersFactory;
use Akeneo\Component\Batch\Model\JobInstance;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Update a job instance
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobInstanceUpdater implements ObjectUpdaterInterface
{
    /** @var JobParametersFactory */
    protected $jobParametersFactory;

    /** @var ContainerInterface */
    private $container;

    /**
     * @param JobParametersFactory $jobParametersFactory
     * @param ContainerInterface   $container
     */
    public function __construct(JobParametersFactory $jobParametersFactory, ContainerInterface $container)
    {
        $this->jobParametersFactory = $jobParametersFactory;
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     *
     * @param JobInstance $jobInstance
     */
    public function update($jobInstance, array $data, array $options = [])
    {
        if (!$jobInstance instanceof JobInstance) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a "Akeneo\Component\Batch\Model\JobInstance", "%s" provided.',
                    ClassUtils::getClass($jobInstance)
                )
            );
        }

        foreach ($data as $field => $value) {
            $this->setData($jobInstance, $field, $value);
        }
    }

    /**
     * @param JobInstance $jobInstance
     * @param string      $field
     * @param mixed       $data
     */
    protected function setData(JobInstance $jobInstance, $field, $data)
    {
        switch ($field) {
            case 'connector':
                $jobInstance->setConnector($data);
                break;
            case 'alias':
                $jobInstance->setAlias($data);
                break;
            case 'label':
                $jobInstance->setLabel($data);
                break;
            case 'type':
                $jobInstance->setType($data);
                break;
            case 'configuration':
                /** @var Job */
                $job = $this->getConnectorRegistry()->getJob($jobInstance);
                /** @var JobParameters $jobParameters */
                $jobParameters = $this->jobParametersFactory->create($job, $data);
                $jobInstance->setRawConfiguration($jobParameters->all());
                break;
            case 'code':
                $jobInstance->setCode($data);
                break;
        }
    }

    /**
     * Should be changed with TIP-418, here we work around a circular reference due to the way we instanciate the whole
     * Job classes in the DIC
     *
     * @return ConnectorRegistry
     */
    protected final function getConnectorRegistry()
    {
        return $this->container->get('akeneo_batch.connectors');
    }
}
