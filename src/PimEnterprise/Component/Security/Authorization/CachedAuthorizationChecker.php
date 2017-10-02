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

namespace PimEnterprise\Component\Security\Authorization;

use Pim\Component\Catalog\Model\ReferableInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Decorates the authorization checker from symfony to be able to cache results.
 *
 * @author Willy MESNAGE <willy.mesnage@akeneo.com>
 */
class CachedAuthorizationChecker implements AuthorizationCheckerInterface
{
    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    /** @var array */
    private $cachedResults;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /**
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param TokenStorageInterface         $tokenStorage
     */
    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        TokenStorageInterface $tokenStorage
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->tokenStorage = $tokenStorage;
        $this->cachedResults = [];
    }

    /**
     * Not referable and null objects can't be cached because we can't guarantee a unique index for similar objects.
     *
     * {@inheritdoc}
     */
    public function isGranted($attributes, $object = null): bool
    {
        if (null === $object || (is_object($object) && !$object instanceof ReferableInterface)) {
            return $this->authorizationChecker->isGranted($attributes, $object);
        }

        $index = $this->getArgumentsAsIndex($attributes, $object);
        if (!array_key_exists($index, $this->cachedResults)) {
            $isGranted = $this->authorizationChecker->isGranted($attributes, $object);
            $this->cachedResults[$index] = $isGranted;
        }

        return $this->cachedResults[$index];
    }

    /**
     * Returns arguments as a string
     *
     * @param array|string $attributes
     * @param mixed        $object
     *
     * @return string
     */
    private function getArgumentsAsIndex($attributes, $object): string
    {
        $userId = $this->getCurrentUserAsIndex();
        $attributesAsIndex = $this->getAttributesAsIndex($attributes);
        $objectAsIndex = $this->getObjectAsIndex($object);

        return sprintf('%s_%s_%s', $userId, $attributesAsIndex, $objectAsIndex);
    }

    /**
     * Get object as string to build the cache index.
     *
     * @param mixed $object
     *
     * @return string
     */
    private function getObjectAsIndex($object): string
    {
        if (is_object($object)) {
            $class = get_class($object);
            $reference = $object->getReference();

            $objectIndex = sprintf('%s_%s', $class, $reference);
        } else {
            $objectIndex = (string) $object;
        }

        return $objectIndex;
    }

    /**
     * Get attributes as string to build the cache index.
     *
     * @param string|array $attributes
     *
     * @return string
     */
    private function getAttributesAsIndex($attributes): string
    {
        if (is_array($attributes)) {
            sort($attributes);
            $attributes = implode('_', $attributes);
        }

        return $attributes;
    }

    /**
     * Returns the current user index.
     *
     * @return string
     */
    private function getCurrentUserAsIndex(): string
    {
        $user = $this->tokenStorage->getToken()->getUser();

        return sprintf('%s_%s', $user->getUsername(), (string) $user->getId());
    }
}
