<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\Security\Updater;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Util\ClassUtils;
use Pim\Bundle\CatalogBundle\Repository\GroupRepositoryInterface;
use Pim\Component\Catalog\Model\GroupInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use PimEnterprise\Component\Security\Model\LocaleAccessInterface;

/**
 * Updates a Locale Access
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class LocaleAccessUpdater implements ObjectUpdaterInterface
{
    /** @var GroupRepositoryInterface */
    protected $groupRepository;

    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /**
     * @param IdentifiableObjectRepositoryInterface $groupRepository
     * @param IdentifiableObjectRepositoryInterface $localeRepository
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $groupRepository,
        IdentifiableObjectRepositoryInterface $localeRepository
    ) {
        $this->groupRepository  = $groupRepository;
        $this->localeRepository = $localeRepository;
    }

    /**
     * {@inheritdoc}
     *
     * Expected input format :
     * [
     *      'locale'        => 'en_US',
     *      'userGroup'     => 'IT Manager'
     *      'view_products' => true,
     *      'edit_products' => false,
     * ]
     */
    public function update($localeAccess, array $data, array $options = [])
    {
        if (!$localeAccess instanceof LocaleAccessInterface) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Expects a "PimEnterprise\Component\Security\Model\LocaleAccessInterface", "%s" provided.',
                    ClassUtils::getClass($localeAccess)
                )
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
     * @throws \InvalidArgumentException
     */
    protected function setData(LocaleAccessInterface $localeAccess, $field, $data)
    {
        if ('locale' == $field) {
            $locale = $this->findLocale($data);
            if (null !== $locale) {
                $localeAccess->setLocale($locale);
            } else {
                throw new \InvalidArgumentException(sprintf('Locale with "%s" code does not exist', $data));
            }
        } elseif ('userGroup' == $field) {
            $group = $this->findGroup($data);
            if (null !== $group) {
                $localeAccess->setUserGroup($group);
            } else {
                throw new \InvalidArgumentException(sprintf('Group with "%s" code does not exist', $data));
            }
        } elseif ('view_products' == $field) {
            $localeAccess->setViewProducts($data);
        } elseif ('edit_products' == $field) {
            $localeAccess->setEditProducts($data);
        }
    }

    /**
     * @param string $code
     *
     * @return GroupInterface|null
     */
    protected function findGroup($code)
    {
        return $this->groupRepository->findOneByIdentifier($code);
    }

    /**
     * @param string $code
     *
     * @return LocaleInterface|null
     */
    protected function findLocale($code)
    {
        return $this->localeRepository->findOneByIdentifier($code);
    }
}
