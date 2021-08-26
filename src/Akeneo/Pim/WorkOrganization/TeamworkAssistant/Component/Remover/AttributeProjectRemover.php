<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Remover;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Model\ProjectInterface;
use Akeneo\Tool\Component\StorageUtils\Detacher\ObjectDetacherInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\Persistence\ObjectRepository;

/**
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class AttributeProjectRemover implements ProjectRemoverInterface
{
    /** @var RemoverInterface */
    protected $projectRemover;

    /** @var ObjectRepository */
    protected $projectRepository;

    /** @var ObjectDetacherInterface */
    protected $detacher;

    /**
     * @param ObjectRepository        $projectRepository
     * @param RemoverInterface        $projectRemover
     * @param ObjectDetacherInterface $detacher
     */
    public function __construct(
        ObjectRepository $projectRepository,
        RemoverInterface $projectRemover,
        ObjectDetacherInterface $detacher
    ) {
        $this->projectRepository = $projectRepository;
        $this->projectRemover = $projectRemover;
        $this->detacher = $detacher;
    }

    /**
     * A project must be removed if an attribute used as product filter is removed.
     *
     * {@inheritdoc}
     */
    public function removeProjectsImpactedBy($attribute, $action = null)
    {
        $attributeCode = $attribute->getCode();
        foreach ($this->projectRepository->findAll() as $project) {
            if ($this->hasToBeRemoved($project, $attributeCode)) {
                $this->projectRemover->remove($project);
            } else {
                $this->detacher->detach($project);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function isSupported($attribute, $action = null)
    {
        return $attribute instanceof AttributeInterface && StorageEvents::PRE_REMOVE === $action;
    }

    /**
     * @param ProjectInterface $project
     * @param string           $attributeCode
     *
     * @return bool
     */
    protected function hasToBeRemoved(ProjectInterface $project, $attributeCode)
    {
        $filters = $project->getProductFilters();
        foreach ($filters as $filter) {
            if ($attributeCode === $filter['field']) {
                return true;
            }
        }

        return false;
    }
}
