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

namespace Akeneo\Pim\Permission\Component\Manager;

use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface;
use Akeneo\Pim\Enrichment\Component\Product\Manager\AttributeValuesResolverInterface;
use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Resolves expected values for attributes, according to the granted permissions.
 *
 * @author Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 */
class AttributeValuesResolver implements AttributeValuesResolverInterface
{
    /** @var AttributeValuesResolverInterface */
    protected $attributeValuesResolver;

    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $attributeRepository;

    /** @var IdentifiableObjectRepositoryInterface */
    protected $localeRepository;

    /**
     * @param AttributeValuesResolverInterface      $attributeValuesResolver
     * @param AuthorizationCheckerInterface         $authorizationChecker
     * @param IdentifiableObjectRepositoryInterface $attributeRepository
     * @param IdentifiableObjectRepositoryInterface $localeRepository
     */
    public function __construct(
        AttributeValuesResolverInterface $attributeValuesResolver,
        AuthorizationCheckerInterface $authorizationChecker,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        IdentifiableObjectRepositoryInterface $localeRepository
    ) {
        $this->attributeValuesResolver = $attributeValuesResolver;
        $this->authorizationChecker = $authorizationChecker;
        $this->attributeRepository = $attributeRepository;
        $this->localeRepository = $localeRepository;
    }

    /**
     * TODO: @merge delete this service and its definition as permissions are handled by repositories
     *
     * Resolves an array of values that are expected to link product to an attribute depending on locale and scope
     * Each value is returned as an array with 'attribute', 'type', 'scope' and 'locale' keys.
     *
     * Only product values granted by permissions are returned.
     *
     * @param AttributeInterface[] $attributes Attributes to resolve
     * @param ChannelInterface[]   $channels   Context channels (all channels by default)
     * @param LocaleInterface[]    $locales    Context locales (all locales by default)
     *
     * @return array:array
     */
    public function resolveEligibleValues(array $attributes, array $channels = null, array $locales = null) : array
    {
        $values = $this->attributeValuesResolver->resolveEligibleValues($attributes, $channels, $locales);

        $grantedValues = [];
        foreach ($values as $value) {
            if ($this->isGrantedAttribute($value['attribute']) && $this->isGrantedLocale($value['locale'])) {
                $grantedValues[] = $value;
            }
        }

        return $grantedValues;
    }


    /**
     * Check if attribute is granted
     *
     * @param string $attributeCode
     *
     * @return bool
     */
    private function isGrantedAttribute(string $attributeCode) : bool
    {
        $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);
        if (null === $attribute) {
            return false;
        }

        return $this->authorizationChecker->isGranted(Attributes::VIEW_ATTRIBUTES, $attribute);
    }

    /**
     * Check if locale is granted
     *
     * @param string $localeCode
     *
     * @return bool
     */
    private function isGrantedLocale(?string $localeCode) : bool
    {
        if (null === $localeCode) {
            return true;
        }

        $locale = $this->localeRepository->findOneByIdentifier($localeCode);
        if (null === $locale) {
            return false;
        }

        return $this->authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $locale);
    }
}
