<?php

namespace Pim\Component\Connector\Processor\Denormalization;

use Akeneo\Bundle\BatchBundle\Connector\ConnectorRegistry;
use Akeneo\Component\Batch\Job\JobParameters;
use Akeneo\Component\Batch\Job\JobParametersFactory;
use Akeneo\Component\Batch\Job\JobParametersValidator;
use Akeneo\Component\Batch\Model\JobInstance;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Job instance processor
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobInstanceProcessor extends AbstractProcessor
{
    /** @var ArrayConverterInterface */
    protected $converter;

    /** @var SimpleFactoryInterface */
    protected $factory;

    /** @var ObjectUpdaterInterface */
    protected $updater;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var ObjectDetacherInterface */
    protected $objectDetacher;

    /** @var JobParametersValidator */
    protected $jobParamsValidator;

    /** @var JobParametersFactory */
    protected $jobParamsFactory;

    /** @var ContainerInterface */
    private $container;

    /**
     * @param IdentifiableObjectRepositoryInterface $repository
     * @param ArrayConverterInterface               $converter
     * @param SimpleFactoryInterface                $factory
     * @param ObjectUpdaterInterface                $updater
     * @param ValidatorInterface                    $validator
     * @param ObjectDetacherInterface               $objectDetacher
     * @param JobParametersValidator                $jobParamsValidator
     * @param JobParametersFactory                  $jobParamsFactory
     * @param ContainerInterface                    $container
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $repository,
        ArrayConverterInterface $converter,
        SimpleFactoryInterface $factory,
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator,
        ObjectDetacherInterface $objectDetacher,
        JobParametersValidator $jobParamsValidator,
        JobParametersFactory $jobParamsFactory,
        ContainerInterface $container
    ) {
        parent::__construct($repository);

        $this->converter      = $converter;
        $this->factory        = $factory;
        $this->updater        = $updater;
        $this->validator      = $validator;
        $this->objectDetacher = $objectDetacher;
        $this->jobParamsValidator = $jobParamsValidator;
        $this->jobParamsFactory = $jobParamsFactory;
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        $convertedItem = $this->converter->convert($item);
        $entity        = $this->findOrCreateObject($convertedItem);

        try {
            $this->updater->update($entity, $convertedItem);
        } catch (\InvalidArgumentException $exception) {
            $this->skipItemWithMessage($item, $exception->getMessage(), $exception);
        }

        $violations = $this->validator->validate($entity);
        if ($violations->count() > 0) {
            $this->objectDetacher->detach($entity);
            $this->skipItemWithConstraintViolations($item, $violations);
        }

        $rawConfiguration = $entity->getRawConfiguration();
        if (!empty($rawConfiguration)) {
            $job = $this->getConnectorRegistry()->getJob($entity);
            $parameters = $this->jobParamsFactory->create($job, $rawConfiguration);
            $violations = $this->jobParamsValidator->validate($job, $parameters);
            if ($violations->count() > 0) {
                $this->objectDetacher->detach($entity);
                $this->skipItemWithConstraintViolations($item, $violations);
            }
        }

        return $entity;
    }

    /**
     * @param array $convertedItem
     *
     * @return JobInstance
     */
    protected function findOrCreateObject(array $convertedItem)
    {
        $entity = $this->findObject($this->repository, $convertedItem);
        if (null === $entity) {
            return $this->factory->create();
        }

        return $entity;
    }

    /**
     * Should be changed with TIP-418, here we work around a circular reference due to the way we instanciate the whole
     * Job classes in the DIC
     *
     * @return ConnectorRegistry
     */
    final protected function getConnectorRegistry()
    {
        return $this->container->get('akeneo_batch.connectors');
    }
}
