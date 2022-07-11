<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Component\Updater;

use Akeneo\Pim\Permission\Component\Model\LocaleAccessInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidPropertyException;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Util\ClassUtils;

/**
 * Updates a Locale Access
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class LocaleAccessUpdater implements ObjectUpdaterInterface
{
    /** @var IdentifiableObjectRepositoryInterface */
    protected $groupRepository;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $localeRepository;

    /**
     * @param IdentifiableObjectRepositoryInterface $groupRepository
     * @param IdentifiableObjectRepositoryInterface $localeRepository
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $groupRepository,
        IdentifiableObjectRepositoryInterface $localeRepository
    ) {
        $this->groupRepository = $groupRepository;
        $this->localeRepository = $localeRepository;
    }

    /**
     * {@inheritdoc}
     *
     * Expected input format :
     * [
     *      'locale'        => 'en_US',
     *      'user_group'     => 'IT Manager'
     *      'view_products' => true,
     *      'edit_products' => false,
     * ]
     */
    public function update($localeAccess, array $data, array $options = [])
    {
        if (!$localeAccess instanceof LocaleAccessInterface) {
            throw InvalidObjectException::objectExpected(
                ClassUtils::getClass($localeAccess),
                LocaleAccessInterface::class
            );
        }

        foreach ($data as $field => $value) {
            $this->setData($localeAccess, $field, $value);
        }

        return $this;
    }

    /**
     * @param LocaleAccessInterface $localeAccess
     * @param string                $field
     * @param mixed                 $data
     *
     * @throws InvalidPropertyException
     */
    protected function setData(LocaleAccessInterface $localeAccess, $field, $data)
    {
        switch ($field) {
            case 'locale':
                $locale = $this->localeRepository->findOneByIdentifier($data);
                if (null === $locale) {
                    throw InvalidPropertyException::validEntityCodeExpected(
                        'locale',
                        'locale code',
                        'The locale does not exist',
                        static::class,
                        $data
                    );
                }
                $localeAccess->setLocale($locale);
                break;
            case 'user_group':
                $group = $this->groupRepository->findOneByIdentifier($data);
                if (null === $group) {
                    throw InvalidPropertyException::validEntityCodeExpected(
                        'user_group',
                        'group code',
                        'The group does not exist',
                        static::class,
                        $data
                    );
                }
                $localeAccess->setUserGroup($group);
                break;
            case 'view_products':
                $localeAccess->setViewProducts($data);
                break;
            case 'edit_products':
                if (true === $data) {
                    $localeAccess->setViewProducts($data);
                }

                $localeAccess->setEditProducts($data);
                break;
        }
    }
}
