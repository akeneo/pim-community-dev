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

use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\Permission\Component\NotGrantedDataFilterInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Akeneo\Tool\Component\StorageUtils\Repository\CachedObjectRepositoryInterface;
use Doctrine\Common\Util\ClassUtils;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyVariantInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Filter not granted values
 *
 * @author Marie Bochu <marie.bochu@akeneo.com>
 */
class NotGrantedValuesFilter implements NotGrantedDataFilterInterface
{
    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    /** @var CachedObjectRepositoryInterface */
    private $localeRepository;

    /**
     * @param AuthorizationCheckerInterface   $authorizationChecker
     * @param CachedObjectRepositoryInterface $localeRepository
     */
    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        CachedObjectRepositoryInterface $localeRepository
    ) {
        $this->authorizationChecker = $authorizationChecker;
        $this->localeRepository = $localeRepository;
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

        foreach ($values as $value) {
            if (!$this->authorizationChecker->isGranted(Attributes::VIEW_ATTRIBUTES, $value->getAttribute())) {
                $values->remove($value);

                continue;
            }

            if (null === $value->getLocale()) {
                continue;
            }

            $locale = $this->localeRepository->findOneByIdentifier($value->getLocale());
            if (!$this->authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $locale)) {
                $values->remove($value);
            }
        }

        $filteredEntityWithValues->setValues($values);

        return $filteredEntityWithValues;
    }
}
