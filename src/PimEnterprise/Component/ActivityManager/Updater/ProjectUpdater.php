<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\ActivityManager\Updater;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Util\ClassUtils;
use PimEnterprise\Component\ActivityManager\Model\ProjectInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Project updater is able to hydrate a project with given parameters.
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class ProjectUpdater implements ObjectUpdaterInterface
{
    /** @var IdentifiableObjectRepositoryInterface */
    protected $channelRepository;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $localeRepository;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $userRepository;

    /**
     * @param IdentifiableObjectRepositoryInterface $channelRepository
     * @param IdentifiableObjectRepositoryInterface $localeRepository
     * @param IdentifiableObjectRepositoryInterface $userRepository
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $channelRepository,
        IdentifiableObjectRepositoryInterface $localeRepository,
        IdentifiableObjectRepositoryInterface $userRepository
    ) {
        $this->channelRepository = $channelRepository;
        $this->localeRepository = $localeRepository;
        $this->userRepository = $userRepository;
    }

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

        return $this;
    }

    /**
     * @param ProjectInterface $project
     * @param string           $field
     * @param mixed            $value
     */
    protected function setData(ProjectInterface $project, $field, $value)
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
                $user = $this->userRepository->findOneByIdentifier($value);
                $project->setOwner($user);
                break;
            case 'datagrid_view':
                $project->setDatagridView($value);
                break;
            case 'product_filters':
                $project->setProductFilters($value);
                break;
            case 'channel':
                $channel = $this->channelRepository->findOneByIdentifier($value);
                $project->setChannel($channel);
                break;
            case 'locale':
                $locale = $this->localeRepository->findOneByIdentifier($value);
                $project->setLocale($locale);
                break;
        }
    }
}
