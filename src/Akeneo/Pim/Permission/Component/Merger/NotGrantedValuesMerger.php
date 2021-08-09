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

use Akeneo\Channel\Component\Query\PublicApi\Permission\GetAllViewableLocalesForUserInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Permission\Component\NotGrantedDataMergerInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Permission\GetViewableAttributeCodesForUserInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Webmozart\Assert\Assert;

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
    private GetViewableAttributeCodesForUserInterface $getViewableAttributeCodes;
    private GetAllViewableLocalesForUserInterface $getViewableLocaleCodesForUser;
    private TokenStorageInterface $tokenStorage;

    public function __construct(
        GetViewableAttributeCodesForUserInterface $getViewableAttributeCodes,
        GetAllViewableLocalesForUserInterface $getViewableLocaleCodesForUser,
        TokenStorageInterface $tokenStorage
    ) {
        $this->getViewableAttributeCodes = $getViewableAttributeCodes;
        $this->getViewableLocaleCodesForUser = $getViewableLocaleCodesForUser;
        $this->tokenStorage = $tokenStorage;
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

        if (
            $filteredEntityWithValues instanceof EntityWithFamilyVariantInterface &&
            null !== $filteredEntityWithValues->getFamilyVariant()
        ) {
            Assert::implementsInterface($fullEntityWithValues, EntityWithFamilyVariantInterface::class);
            $originalValues = WriteValueCollection::fromCollection($fullEntityWithValues->getValuesForVariation());
            $newValues = WriteValueCollection::fromCollection($filteredEntityWithValues->getValuesForVariation());
        } else {
            $originalValues = WriteValueCollection::fromCollection($fullEntityWithValues->getValues());
            $newValues = WriteValueCollection::fromCollection($filteredEntityWithValues->getValues());
        }

        $userId = $this->getUserId();
        if (-1 !== $userId) {
            $grantedAttributeCodes = array_flip(
                $this->getViewableAttributeCodes->forAttributeCodes(
                    $originalValues->getAttributeCodes(),
                    $userId
                )
            );
            $grantedLocaleCodes = $this->getViewableLocaleCodesForUser->fetchAll($userId);

            // Add not granted original values if they don't exist in the new values
            foreach ($originalValues as $key => $originalValue) {
                if ((!isset($grantedAttributeCodes[$originalValue->getAttributeCode()]) ||
                        ($originalValue->isLocalizable() && !in_array($originalValue->getLocaleCode(), $grantedLocaleCodes)))
                    && !$newValues->containsKey($key)
                ) {
                    $newValues->add($originalValue);
                }
            }
        }
        $fullEntityWithValues->setValues($newValues);

        return $fullEntityWithValues;
    }

    private function getUserId(): int
    {
        if (null === $this->tokenStorage->getToken() || null === $this->tokenStorage->getToken()->getUser()) {
            throw new \RuntimeException('Could not find any authenticated user');
        }

        $user = $this->tokenStorage->getToken()->getUser();
        Assert::implementsInterface($user, UserInterface::class);
        if (null === $user->getId()) {
            if (UserInterface::SYSTEM_USER_NAME === $user->getUsername()) {
                return -1;
            }
            throw new \RuntimeException('Could not find any authenticated user');
        }

        return $user->getId();
    }
}
