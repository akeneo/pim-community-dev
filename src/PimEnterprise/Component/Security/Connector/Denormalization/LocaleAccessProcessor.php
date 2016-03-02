<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\Security\Connector\Denormalization;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Pim\Component\Connector\ArrayConverter\StandardArrayConverterInterface;
use Pim\Component\Connector\Exception\MissingIdentifierException;
use Pim\Component\Connector\Processor\Denormalization\AbstractProcessor;
use PimEnterprise\Component\Security\Factory\LocaleAccessFactory;
use PimEnterprise\Component\Security\Model\LocaleAccessInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Locale Access processor
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
class LocaleAccessProcessor extends AbstractProcessor
{
    /** @var StandardArrayConverterInterface */
    protected $accessConverter;

    /** @var LocaleAccessFactory */
    protected $accessFactory;

    /** @var ObjectUpdaterInterface */
    protected $updater;

    /** @var ValidatorInterface */
    protected $validator;

    /**
     * @param IdentifiableObjectRepositoryInterface $repository
     * @param StandardArrayConverterInterface $accessConverter
     * @param LocaleAccessFactory $accessFactory
     * @param ObjectUpdaterInterface $updater
     * @param ValidatorInterface $validator
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $repository,
        StandardArrayConverterInterface $accessConverter,
        LocaleAccessFactory $accessFactory,
        ObjectUpdaterInterface $updater,
        ValidatorInterface $validator
    ) {
        parent::__construct($repository);

        $this->accessConverter = $accessConverter;
        $this->accessFactory   = $accessFactory;
        $this->updater         = $updater;
        $this->validator       = $validator;
    }

    /**
     * {@inheritdoc}
     *
     * Warning, this process method only updates existing locale accesses or create new ones. It does not delete
     * existing locale accesses.
     */
    public function process($item)
    {
        $convertedItem = $this->accessConverter->convert($item);


        $localeAccesses = [];
        foreach ($this->flatItem($convertedItem) as $flattenItem) {
            $localeAccess = $this->findOrCreateLocaleAccess($flattenItem);

            try {
                $this->updater->update($localeAccess, $flattenItem);
            } catch (\InvalidArgumentException $exception) {
                $this->skipItemWithMessage($item, $exception->getMessage(), $exception);
            }

            $violations = $this->validator->validate($localeAccess);
            if ($violations->count() > 0) {
                $this->skipItemWithConstraintViolations($item, $violations);
            }

            $localeAccesses[] = $localeAccess;
        }

        return $localeAccesses;
    }

    /**
     * Convert a single item containing information about access into a set of locale accesses by locale and user group.
     * The result can be send to an updater.
     *
     * Transforms
     * [
     *     'locale'        => 'en_US',
     *     'view_products' => ['Manager', 'Redactor'],
     *     'edit_products' => ['Manager'],
     * ]
     * into
     * [
     *     [
     *         'locale'        => 'en_US',
     *         'userGroup'     => 'Manager',
     *         'view_products' => true,
     *         'edit_products' => true,
     *     ], [
     *         'locale'        => 'en_US',
     *         'userGroup'     => 'Redactor',
     *         'view_products' => true,
     *         'edit_products' => false,
     *     ]
     * }
     *
     * @param $item
     *
     * @return array
     */
    protected function flatItem($item)
    {
        $permissions = ['view_products', 'edit_products'];
        $userGroupNames = [];
        foreach ($permissions as $permission) {
            foreach ($item[$permission] as $userGroupName) {
                if (!in_array($userGroupName, $userGroupNames)){
                    $userGroupNames[] = $userGroupName;
                }
            }
        }

        $items = [];
        foreach ($userGroupNames as $userGroupName) {
            $newItem = [
                'locale'    => $item['locale'],
                'userGroup' => $userGroupName
            ];
            foreach ($permissions as $permission) {
                $newItem[$permission] = in_array($userGroupName, $item[$permission]);
            }
            $items[] = $newItem;
        }

        return $items;
    }

    /**
     * @param array $convertedItem
     *
     * @return LocaleAccessInterface
     *
     * @throws MissingIdentifierException
     */
    protected function findOrCreateLocaleAccess(array $convertedItem)
    {
        $localeAccess = $this->findObject($this->repository, $convertedItem);
        if (null === $localeAccess) {
            return $this->accessFactory->create();
        }

        return $localeAccess;
    }
}
