<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Component\Filter;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Permission\Component\NotGrantedDataFilterInterface;
use Akeneo\Pim\Permission\Component\Query\GetAllViewableLocalesForUser;
use Akeneo\Pim\Permission\Component\Query\GetViewableAttributeCodesForUserInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Filter not granted values
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class NotGrantedValuesFilter implements NotGrantedDataFilterInterface
{
    /** @var GetViewableAttributeCodesForUserInterface */
    private $getViewableAttributeCodes;

    /** @var GetAllViewableLocalesForUser */
    private $getViewableLocaleCodesForUser;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    public function __construct(
        GetViewableAttributeCodesForUserInterface $getViewableAttributeCodes,
        GetAllViewableLocalesForUser $getViewableLocaleCodesForUser,
        TokenStorageInterface $tokenStorage
    ) {
        $this->getViewableAttributeCodes = $getViewableAttributeCodes;
        $this->getViewableLocaleCodesForUser = $getViewableLocaleCodesForUser;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function filter($entityWithValues)
    {
        if (!$entityWithValues instanceof EntityWithValuesInterface) {
            throw InvalidObjectException::objectExpected(ClassUtils::getClass($entityWithValues), EntityWithValuesInterface::class);
        }

        $filteredEntityWithValues = clone $entityWithValues;

        if ($filteredEntityWithValues instanceof EntityWithFamilyVariantInterface &&
            null !== $filteredEntityWithValues->getFamilyVariant()) {
            $values = clone $filteredEntityWithValues->getValuesForVariation();
        } else {
            $values = clone $filteredEntityWithValues->getValues();
        }

        $userId = $this->getUserId();
        $grantedAttributeCodes = array_flip(
            $this->getViewableAttributeCodes->forAttributeCodes($values->getAttributeCodes(), $userId)
        );
        $grantedLocaleCodes = $this->getViewableLocaleCodesForUser->fetchAll($userId);

        foreach ($values as $value) {
            if (!isset($grantedAttributeCodes[$value->getAttributeCode()])) {
                $values->remove($value);

                continue;
            }
            if (null !== $value->getLocaleCode() && !in_array($value->getLocaleCode(), $grantedLocaleCodes)) {
                $values->remove($value);
            }
        }

        $filteredEntityWithValues->setValues($values);

        return $filteredEntityWithValues;
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
