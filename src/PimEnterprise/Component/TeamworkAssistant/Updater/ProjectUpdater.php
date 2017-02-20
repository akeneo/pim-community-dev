<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\TeamworkAssistant\Updater;

use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Catalog\Exception\InvalidArgumentException;
use PimEnterprise\Component\TeamworkAssistant\Model\ProjectInterface;

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
            throw InvalidObjectException::objectExpected(
                ClassUtils::getClass($project),
                ProjectInterface::class
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
                    $this->validateDateFormat($field, $value);
                    $dateTime = new \DateTime($value);
                } else {
                    $dateTime = null;
                }

                $project->setDueDate($dateTime);
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
                if (!$locale->isActivated()) {
                    throw InvalidPropertyException::dataExpected(
                        $field,
                        'to be activated',
                        static::class,
                        gettype($value)
                    );
                }
                $project->setLocale($locale);
                break;
        }
    }

    /**
     * @param string $field
     * @param string $data
     *
     * @throws InvalidArgumentException
     */
    protected function validateDateFormat($field, $data)
    {
        try {
            new \DateTime($data);

            if (!preg_match('/^\d{4}-\d{2}-\d{2}/', $data)) {
                throw new \Exception('Invalid date');
            }
        } catch (\Exception $e) {
            throw InvalidPropertyException::dataExpected(
                $field,
                'a string with the format yyyy-mm-dd',
                static::class,
                gettype($data)
            );
        }
    }
}
