<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Updater;

use Akeneo\Pim\Enrichment\Component\Product\Exception\InvalidArgumentException;
use Akeneo\Pim\WorkOrganization\TeamworkAssistant\Component\Model\ProjectInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Util\ClassUtils;
use Oro\Bundle\PimDataGridBundle\Entity\DatagridView;

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
                if (null === $user) {
                    throw InvalidPropertyException::validEntityCodeExpected(
                        $field,
                        'owner username',
                        'The owner username does not exist',
                        static::class,
                        $value
                    );
                }
                $project->setOwner($user);
                break;
            case 'datagrid_view':
                if (!$value instanceof DatagridView) {
                    throw new InvalidPropertyException(
                        $field,
                        $value,
                        static::class,
                        sprintf('Wrong datagrid view type: given %s, expected %s', gettype($value), DatagridView::class)
                    );
                }
                $project->setDatagridView($value);
                break;
            case 'product_filters':
                if (!is_array($value)) {
                    throw new InvalidPropertyException(
                        $field,
                        $value,
                        static::class,
                        sprintf('Product filters must be an array, given %s,', gettype($value))
                    );
                }
                $project->setProductFilters($value);
                break;
            case 'channel':
                $channel = $this->channelRepository->findOneByIdentifier($value);
                if (null === $channel) {
                    throw InvalidPropertyException::validEntityCodeExpected(
                        $field,
                        'channel code',
                        'The channel code does not exist',
                        static::class,
                        $value
                    );
                }
                $project->setChannel($channel);
                break;
            case 'locale':
                $locale = $this->localeRepository->findOneByIdentifier($value);
                if (null === $locale) {
                    throw InvalidPropertyException::validEntityCodeExpected(
                        $field,
                        'locale code',
                        'The locale code does not exist',
                        static::class,
                        $value
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
                static::class
            );
        }
    }
}
