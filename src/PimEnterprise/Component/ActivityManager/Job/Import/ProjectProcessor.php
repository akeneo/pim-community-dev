<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ActivityManager\Job\Import;

use Akeneo\Component\Batch\Item\ItemProcessorInterface;
use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;
use Akeneo\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Pim\Component\Catalog\Exception\MissingIdentifierException;
use Pim\Component\Connector\Processor\Denormalization\AbstractProcessor;
use PimEnterprise\Component\ActivityManager\Factory\ProjectFactoryInterface;
use PimEnterprise\Component\ActivityManager\Model\ProjectIdentifier;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Build or update a project from flat data
 *
 * @author Arnaud Langlade <arnaud.langlade@akeneo.com>
 */
class ProjectProcessor extends AbstractProcessor implements ItemProcessorInterface, StepExecutionAwareInterface
{
    /** @var IdentifiableObjectRepositoryInterface */
    protected $projectRepository;

    /** @var ProjectFactoryInterface */
    protected $projectFactory;

    /** @var ObjectUpdaterInterface */
    protected $projectUpdater;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var ObjectDetacherInterface */
    protected $objectDetacher;

    /**
     * @param IdentifiableObjectRepositoryInterface $projectRepository
     * @param ProjectFactoryInterface               $projectFactory
     * @param ObjectUpdaterInterface                $projectUpdater
     * @param ValidatorInterface                    $validator
     * @param ObjectDetacherInterface               $objectDetacher
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $projectRepository,
        ProjectFactoryInterface $projectFactory,
        ObjectUpdaterInterface $projectUpdater,
        ValidatorInterface $validator,
        ObjectDetacherInterface $objectDetacher
    ) {
        $this->projectRepository = $projectRepository;
        $this->projectFactory = $projectFactory;
        $this->projectUpdater = $projectUpdater;
        $this->validator = $validator;
        $this->objectDetacher = $objectDetacher;
    }

    /**
     * {@inheritdoc}
     *
     * In case of the project does not exist we build it, that means that the project and datagrid view are created.
     * Otherwise, we update the project properties the datagrid view can not be updated.
     */
    public function process($projectData)
    {
        $project = $this->findOrCreateProject($projectData);

        $violations = $this->validator->validate($project);
        if ($violations->count() > 0) {
            $this->objectDetacher->detach($project);
            $this->skipItemWithConstraintViolations($projectData, $violations);
        }

        return $project;
    }

    /**
     * Return a project with its updated properties.
     *
     * @param array $projectData
     *
     * @return ProjectInterface
     */
    protected function findOrCreateProject(array $projectData)
    {
        $project = $this->projectRepository->findOneByIdentifier($this->generateProjectCode($projectData));

        try {
            if (null === $project) {
                $project = $this->projectFactory->create($projectData);
            } else {
                $this->projectUpdater->update($project, $projectData);
            }
        } catch (\InvalidArgumentException $exception) {
            $this->skipItemWithMessage($projectData, $exception->getMessage(), $exception);
        }

        return $project;
    }

    /**
     * Generate the project code from the project label, channel and the locale.
     *
     * @param array $projectData
     *
     * @throws MissingIdentifierException
     *
     * @return string
     */
    protected function generateProjectCode(array $projectData)
    {
        if (!isset($projectData['label']) || !isset($projectData['channel']) || !$projectData['locale']) {
            throw new MissingIdentifierException(sprintf(
                'Missing identifier columns "label, channel and locale". Columns found: %s.',
                implode(', ', array_keys($projectData))
            ));
        }

        $projectCode = new ProjectIdentifier(
            $projectData['label'],
            $projectData['channel'],
            $projectData['locale']
        );

        return $projectCode;
    }
}
