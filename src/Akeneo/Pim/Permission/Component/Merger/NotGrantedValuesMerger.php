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

namespace Akeneo\Pim\Permission\Component\Merger;

use Akeneo\Pim\Enrichment\Component\Product\Factory\WriteValueCollectionFactory;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Permission\Component\NotGrantedDataMergerInterface;
use Akeneo\Pim\Permission\Component\Query\GetAllViewableLocalesForUser;
use Akeneo\Pim\Permission\Component\Query\GetViewableAttributeCodesForUserInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

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
 * (@see \Akeneo\Pim\Permission\Component\Factory\ValueCollectionFactory)
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
    /** @var GetViewableAttributeCodesForUserInterface */
    private $getViewableAttributeCodes;

    /** @var GetAllViewableLocalesForUser */
    private $getViewableLocaleCodesForUser;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var WriteValueCollectionFactory */
    private $valueCollectionFactory;

    public function __construct(
        GetViewableAttributeCodesForUserInterface $getViewableAttributeCodes,
        GetAllViewableLocalesForUser $getViewableLocaleCodesForUser,
        TokenStorageInterface $tokenStorage,
        WriteValueCollectionFactory $valueCollectionFactory
    ) {
        $this->getViewableAttributeCodes = $getViewableAttributeCodes;
        $this->getViewableLocaleCodesForUser = $getViewableLocaleCodesForUser;
        $this->tokenStorage = $tokenStorage;
        $this->valueCollectionFactory = $valueCollectionFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function merge($filteredEntityWithValues, $fullEntityWithValues = null)
    {
        if (!$filteredEntityWithValues instanceof EntityWithValuesInterface) {
            throw InvalidObjectException::objectExpected(ClassUtils::getClass($filteredEntityWithValues), EntityWithValuesInterface::class);
        }

        if (null === $fullEntityWithValues) {
            return $filteredEntityWithValues;
        }

        if (!$fullEntityWithValues instanceof EntityWithValuesInterface) {
            throw InvalidObjectException::objectExpected(ClassUtils::getClass($fullEntityWithValues), EntityWithValuesInterface::class);
        }

        $rawValuesToMerge = [];
        $userId = $this->getUserId();

        $grantedAttributeCodes = array_flip(
            $this->getViewableAttributeCodes->forAttributeCodes(
                array_keys($fullEntityWithValues->getRawValues()),
                $userId
            )
        );
        $grantedLocaleCodes = $this->getViewableLocaleCodesForUser->fetchAll($userId);

        foreach ($fullEntityWithValues->getRawValues() as $attributeCode => $values) {
            if (!isset($grantedAttributeCodes[$attributeCode])) {
                $rawValuesToMerge[$attributeCode] = $values;
            } else {
                foreach ($values as $channelCode => $localeRawValue) {
                    foreach ($localeRawValue as $localeCode => $data) {
                        if ('<all_locales>' !== $localeCode && !in_array($localeCode, $grantedLocaleCodes)) {
                            $rawValuesToMerge[$attributeCode][$channelCode][$localeCode] = $data;
                        }
                    }
                }
            }
        }

        if ($filteredEntityWithValues instanceof EntityWithFamilyVariantInterface &&
            null !== $filteredEntityWithValues->getFamilyVariant()
        ) {
            $values = clone $filteredEntityWithValues->getValuesForVariation();
        } else {
            $values = clone $filteredEntityWithValues->getValues();
        }

        $fullEntityWithValues->setValues($values);

        if (!empty($rawValuesToMerge)) {
            $notGrantedValues = $this->valueCollectionFactory->createFromStorageFormat($rawValuesToMerge);

            foreach ($notGrantedValues as $notGrantedValue) {
                $fullEntityWithValues->addValue($notGrantedValue);
            }
        }

        return $fullEntityWithValues;
    }

    private function getUserId(): int
    {
        if (null === $this->tokenStorage->getToken()) {
            throw new \RuntimeException('Could not find any authenticated user');
        }
        $user = $this->tokenStorage->getToken()->getUser();
        if (null === $user || null === $user->getId()) {
            throw new \RuntimeException('Could not find any authenticated user');
        }

        return $user->getId();
    }
}
