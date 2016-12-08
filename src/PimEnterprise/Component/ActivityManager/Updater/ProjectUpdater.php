<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ActivityManager\Component\Updater;

use Akeneo\ActivityManager\Component\Model\ProjectInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Util\ClassUtils;
use Gedmo\Sluggable\Util\Urlizer;

/**
 * Project updater is able to hydrate a project with given parameters.
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class ProjectUpdater implements ObjectUpdaterInterface
{
    /**
     * {@inheritdoc}
     */
    public function update($project, array $data, array $options = [])
    {
        if (!$project instanceof ProjectInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a "%s", "%s" provided.',
                    ProjectInterface::class,
                    ClassUtils::getClass($project)
                )
            );
        }

        foreach ($data as $field => $value) {
            $this->setData($project, $field, $value);
        }

        $this->generateCode($project);

        return $this;
    }

    /**
     * @param ProjectInterface $project
     * @param string           $field
     * @param mixed            $value
     */
    private function setData(ProjectInterface $project, $field, $value)
    {
        switch ($field) {
            case 'label':
                $project->setLabel($value);
                break;
            case 'due_date':
                if (!empty($value)) {
                    $project->setDueDate(new \DateTime($value));
                }
                break;
            case 'description':
                $project->setDescription($value);
                break;
            case 'owner':
                $project->setOwner($value);
                break;
            case 'datagrid_view':
                $project->setDatagridView($value);
                break;
            case 'product_filters':
                $project->setProductFilters($value);
                break;
            case 'channel':
                $project->setChannel($value);
                break;
            case 'locale':
                $project->setLocale($value);
                break;
            case 'user_groups':
                foreach ($value as $userGroup) {
                    $project->addUserGroup($userGroup);
                }
                break;
        }
    }

    /**
     * Generate the project code from the project label, channel and the locale.
     *
     * @param ProjectInterface $project
     */
    private function generateCode(ProjectInterface $project)
    {
        $projectCode = Urlizer::transliterate(
            sprintf(
                '%s %s %s',
                $project->getLabel(),
                $project->getChannel()->getCode(),
                $project->getLocale()->getCode()
            )
        );

        $project->setCode($projectCode);
    }
}
