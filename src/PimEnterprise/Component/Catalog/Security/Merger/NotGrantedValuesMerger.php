<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Component\Catalog\Security\Merger;

use Akeneo\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Util\ClassUtils;
use Pim\Component\Catalog\Factory\ValueCollectionFactoryInterface;
use Pim\Component\Catalog\Model\EntityWithFamilyVariantInterface;
use Pim\Component\Catalog\Model\EntityWithValuesInterface;
use PimEnterprise\Component\Security\Attributes;
use PimEnterprise\Component\Security\NotGrantedDataMergerInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Merge not granted values with new values. Example:
 * In database, your product "my_product" contains those values:
 * {
 *    "values": {
 *      "a_text": [
 *          { "data": "my text", "locale": null, "scope": null }
 *      ],
 *      "a_number": [
 *          { "data": 12, "locale": null, "scope": null }
 *      ],
 *      "a_localizable_text": [
 *          { "data": "my text", "locale": "en_US", "scope": null },
 *          { "data": "mon text", "locale": "fr_FR", "scope": null }
 *      ]
 *    }
 * }
 *
 * But "a_text" belongs to an attribute group not viewable and locale "fr_FR" is not viewable by the connected user.
 * That's means when he will get the product "my_product", the application will return:
 * {
 *    "values": {
 *      "a_number": [
 *          { "data": 12, "locale": null, "scope": null }
 *      ],
 *      "a_localizable_text": [
 *          { "data": "my text", "locale": "en_US", "scope": null }
 *      ]
 *    }
 * }
 * (@see \PimEnterprise\Component\Catalog\Security\Factory\ValueCollectionFactory)
 *
 * When user will update "my_product":
 * {
 *    "values": {
 *      "a_localizable_text": [
 *          { "data": "my english text", "locale": "en_US", "scope": null }
 *      ]
 *    }
 * }
 *
 * We have to merge not granted data (here "a_text" value and "a_localizable_text" with locale "fr_FR") before saving data in database.
 * Finally, "my_product" will contain:
 * {
 *    "values": {
 *      "a_text": [
 *          { "data": "my text", "locale": null, "scope": null }
 *      ],
 *      "a_number": [
 *          { "data": 12, "locale": null, "scope": null }
 *      ],
 *      "a_localizable_text": [
 *          { "data": "my english text", "locale": "en_US", "scope": null },
 *          { "data": "mon text", "locale": "fr_FR", "scope": null }
 *      ]
 *    }
 * }
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class NotGrantedValuesMerger implements NotGrantedDataMergerInterface
{
    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    /** @var IdentifiableObjectRepositoryInterface */
    private $attributeRepository;

    /** @var IdentifiableObjectRepositoryInterface */
    private $localeRepository;

    /** @var ValueCollectionFactoryInterface */
    private $valueCollectionFactory;

    /**
     * @param AuthorizationCheckerInterface         $authorizationChecker
     * @param IdentifiableObjectRepositoryInterface $attributeRepository
     * @param IdentifiableObjectRepositoryInterface $localeRepository
     * @param ValueCollectionFactoryInterface       $valueCollectionFactory
     */
    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        IdentifiableObjectRepositoryInterface $localeRepository,
        ValueCollectionFactoryInterface $valueCollectionFactory
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->attributeRepository = $attributeRepository;
        $this->localeRepository = $localeRepository;
        $this->valueCollectionFactory = $valueCollectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function merge($filteredProduct, $fullProduct)
    {
        if (!$filteredProduct instanceof EntityWithValuesInterface) {
            throw InvalidObjectException::objectExpected(ClassUtils::getClass($filteredProduct), EntityWithValuesInterface::class);
        }

        if (!$fullProduct instanceof EntityWithValuesInterface) {
            throw InvalidObjectException::objectExpected(ClassUtils::getClass($fullProduct), EntityWithValuesInterface::class);
        }

        $rawValuesToMerge = [];
        foreach ($fullProduct->getRawValues() as $attributeCode => $values) {
            $isGrantedAttribute = $this->isGrantedAttribute($attributeCode);
            if (null !== $isGrantedAttribute && false === $isGrantedAttribute) {
                $rawValuesToMerge[$attributeCode] = $values;
            } else {
                $notGrantedValuesLocalizable = $this->getNotGrantedValuesLocalizable($values);

                if (!empty($notGrantedValuesLocalizable)) {
                    $rawValuesToMerge[$attributeCode] = $notGrantedValuesLocalizable;
                }
            }
        }

        if ($filteredProduct instanceof EntityWithFamilyVariantInterface) {
            $values = clone $filteredProduct->getValuesForVariation();
        } else {
            $values = clone $filteredProduct->getValues();
        }

        $fullProduct->setValues($values);

        if (!empty($rawValuesToMerge)) {
            $notGrantedValues = $this->valueCollectionFactory->createFromStorageFormat($rawValuesToMerge);

            foreach ($notGrantedValues as $notGrantedValue) {
                $fullProduct->addValue($notGrantedValue);
            }
        }

        return $fullProduct;
    }

    /**
     * @param array $values
     *
     * @return array
     */
    private function getNotGrantedValuesLocalizable(array $values): array
    {
        $notGrantedValues = [];

        foreach ($values as $channelCode => $localeRawValue) {
            foreach ($localeRawValue as $localeCode => $data) {
                if ('<all_locales>' !== $localeCode) {
                    $locale = $this->localeRepository->findOneByIdentifier($localeCode);
                    if (null !== $locale && !$this->authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $locale)) {
                        $notGrantedValues[$channelCode][$localeCode] = $data;
                    }
                }
            }
        }

        return $notGrantedValues;
    }

    /**
     * @param mixed $attributeCode
     *
     * @return bool|null
     */
    private function isGrantedAttribute($attributeCode): ?bool
    {
        $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);
        if (null === $attribute) {
            return null;
        }

        return $this->authorizationChecker->isGranted(Attributes::VIEW_ATTRIBUTES, $attribute);
    }
}
