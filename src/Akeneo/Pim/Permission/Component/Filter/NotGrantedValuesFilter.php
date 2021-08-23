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

use Akeneo\Channel\Component\Query\PublicApi\Permission\GetAllViewableLocalesForUserInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Permission\Component\NotGrantedDataFilterInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\Permission\GetViewableAttributeCodesForUserInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\UserManagement\Component\Model\UserInterface;
use Doctrine\Common\Util\ClassUtils;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Webmozart\Assert\Assert;

/**
 * Filter not granted values
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class NotGrantedValuesFilter implements NotGrantedDataFilterInterface
{
    private GetViewableAttributeCodesForUserInterface $getViewableAttributeCodes;
    private GetAllViewableLocalesForUserInterface $getViewableLocaleCodesForUser;

    /** @var TokenStorageInterface */
    private $tokenStorage;

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
    public function filter($entityWithValues)
    {
        if (!$entityWithValues instanceof EntityWithValuesInterface) {
            throw InvalidObjectException::objectExpected(ClassUtils::getClass($entityWithValues), EntityWithValuesInterface::class);
        }

        $filteredEntityWithValues = clone $entityWithValues;
        $userId = $this->getUserId();
        if (-1 === $userId) {
            return $filteredEntityWithValues;
        }

        if (
            $filteredEntityWithValues instanceof EntityWithFamilyVariantInterface &&
            null !== $filteredEntityWithValues->getFamilyVariant()
        ) {
            $values = clone $filteredEntityWithValues->getValuesForVariation();
        } else {
            $values = clone $filteredEntityWithValues->getValues();
        }

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
