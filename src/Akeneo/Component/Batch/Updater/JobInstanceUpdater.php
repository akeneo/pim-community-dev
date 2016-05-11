<?php

namespace Akeneo\Component\Batch\Updater;

// TODO TIP-303: bad use of BatchBundle ... anyway the ConnectorRegistry is too complex
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

    /** @var ContainerInterface TODO TIP-303: to fix circular reference, the way we load the whole Job in DI is realllly problematic */
    protected $container;

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
                $jobInstance->setRawConfiguration($jobParameters->getParameters());
                break;
            case 'code':
                $jobInstance->setCode($data);
                break;
        }
    }

    /**
     * @return ConnectorRegistry
     */
    protected function getConnectorRegistry()
    {
        return $this->container->get('akeneo_batch.connectors');
    }
}
