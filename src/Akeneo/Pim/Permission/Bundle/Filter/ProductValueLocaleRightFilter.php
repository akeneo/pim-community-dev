<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Bundle\Filter;

use Akeneo\Channel\Infrastructure\Component\Repository\LocaleRepositoryInterface;
use Akeneo\Pim\Enrichment\Bundle\Filter\CollectionFilterInterface;
use Akeneo\Pim\Enrichment\Bundle\Filter\ObjectFilterInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * If a product value is localizable or locale specific it will be filtered according to locale rights.
 * In case of a locale specific value, the user must have the view rights on at least one of its locales to see it.
 *
 * @author Julien Sanchez <julien@akeneo.com>
 */
class ProductValueLocaleRightFilter extends AbstractAuthorizationFilter implements
    CollectionFilterInterface,
    ObjectFilterInterface
{
    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $cachedLocaleRepository;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $attributeRepository;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker,
        LocaleRepositoryInterface $localeRepository,
        IdentifiableObjectRepositoryInterface $cachedLocaleRepository,
        IdentifiableObjectRepositoryInterface $attributeRepository
    ) {
        parent::__construct($tokenStorage, $authorizationChecker);

        $this->localeRepository = $localeRepository;
        $this->cachedLocaleRepository = $cachedLocaleRepository;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function filterCollection($collection, $type, array $options = [])
    {
        foreach ($collection as $productValue) {
            if ($this->filterObject($productValue, $type, $options)) {
                $collection->remove($productValue);
            }
        }

        return $collection;
    }

    /**
     * {@inheritdoc}
     */
    public function filterObject($value, $type, array $options = [])
    {
        if (!$this->supportsObject($value, $type, $options)) {
            throw new \LogicException('This filter only handles objects of type "ValueInterface"');
        }

        $localeRepository = $this->cachedLocaleRepository ?: $this->localeRepository;

        if ($value->isLocalizable() &&
            !$this->authorizationChecker->isGranted(
                Attributes::VIEW_ITEMS,
                $localeRepository->findOneByIdentifier($value->getLocaleCode())
            )
        ) {
            return true;
        }

        /** @var AttributeInterface $attribute */
        $attribute = $this->attributeRepository->findOneByIdentifier($value->getAttributeCode());

        if (null !== $attribute && $attribute->isLocaleSpecific()) {
            $localeCodes = $attribute->getAvailableLocaleCodes();

            $authorizedLocaleCodes = array_filter(
                $localeCodes,
                function ($localeCode) use ($localeRepository) {
                    return $this->authorizationChecker->isGranted(
                        Attributes::VIEW_ITEMS,
                        $localeRepository->findOneByIdentifier($localeCode)
                    );
                }
            );

            if (empty($authorizedLocaleCodes)) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsCollection($collection, $type, array $options = [])
    {
        return $collection instanceof WriteValueCollection && null !== $this->tokenStorage->getToken();
    }

    /**
     * {@inheritdoc}
     */
    public function supportsObject($object, $type, array $options = [])
    {
        return parent::supportsObject($options, $type, $options) && $object instanceof ValueInterface;
    }
}
