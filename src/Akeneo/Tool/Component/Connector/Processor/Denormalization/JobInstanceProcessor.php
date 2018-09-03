<?php

namespace Akeneo\Tool\Component\Connector\Processor\Denormalization;

use Akeneo\Tool\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Tool\Component\Batch\Job\JobParametersFactory;
use Akeneo\Tool\Component\Batch\Job\JobParametersValidator;
use Akeneo\Tool\Component\Batch\Job\JobRegistry;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Tool\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\PropertyException;
use Akeneo\Tool\Component\StorageUtils\Factory\SimpleFactoryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Job instance processor
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobInstanceProcessor extends AbstractProcessor implements ItemProcessorInterface, StepExecutionAwareInterface
{
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

    /** @var JobRegistry */
    protected $jobRegistry;

    /**
     * @param IdentifiableObjectRepositoryInterface $repository
     * @param SimpleFactoryInterface                $factory
     * @param ObjectUpdaterInterface                $updater
     * @param ValidatorInterface                    $validator
     * @param ObjectDetacherInterface               $objectDetacher
     * @param JobParametersValidator                $jobParamsValidator
     * @param JobParametersFactory                  $jobParamsFactory
     * @param JobRegistry                           $jobRegistry
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $repository,
        SimpleFactoryInterface $factory,
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator,
        ObjectDetacherInterface $objectDetacher,
        JobParametersValidator $jobParamsValidator,
        JobParametersFactory $jobParamsFactory,
        JobRegistry $jobRegistry
    ) {
        parent::__construct($repository);

        $this->factory = $factory;
        $this->updater = $updater;
        $this->validator = $validator;
        $this->objectDetacher = $objectDetacher;
        $this->jobParamsValidator = $jobParamsValidator;
        $this->jobParamsFactory = $jobParamsFactory;
        $this->jobRegistry = $jobRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function process($item)
    {
        $entity = $this->findOrCreateObject($item);

        try {
            $this->updater->update($entity, $item);
        } catch (PropertyException $exception) {
            $this->skipItemWithMessage($item, $exception->getMessage(), $exception);
        }

        $violations = $this->validator->validate($entity);
        if ($violations->count() > 0) {
            $this->objectDetacher->detach($entity);
            $this->skipItemWithConstraintViolations($item, $violations);
        }

        $rawParameters = $entity->getRawParameters();
        if (!empty($rawParameters)) {
            $job = $this->jobRegistry->get($entity->getJobName());
            $parameters = $this->jobParamsFactory->create($job, $rawParameters);
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
}
