<?php
declare(strict_types=1);

namespace PimEnterprise\Component\Catalog\ProductModel\Filter;

use Akeneo\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Pim\Component\Catalog\ProductModel\Filter\AttributeFilterInterface;
use PimEnterprise\Component\Security\Attributes;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Filter granted product values belonging to the parents.
 * A product value is granted if attribute and locale are visible.
 *
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GrantedProductAttributeFilter implements AttributeFilterInterface
{
    /** @var AttributeFilterInterface */
    private $productAttributeFilter;

    /** @var IdentifiableObjectRepositoryInterface */
    private $attributeRepository;

    /** @var IdentifiableObjectRepositoryInterface */
    private $localeRepository;

    /** @var AuthorizationCheckerInterface */
    private $authorizationChecker;

    /**
     * @param AttributeFilterInterface              $productAttributeFilter
     * @param IdentifiableObjectRepositoryInterface $attributeRepository
     * @param IdentifiableObjectRepositoryInterface $localeRepository
     * @param AuthorizationCheckerInterface         $authorizationChecker
     */
    public function __construct(
        AttributeFilterInterface $productAttributeFilter,
        IdentifiableObjectRepositoryInterface $attributeRepository,
        IdentifiableObjectRepositoryInterface $localeRepository,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->productAttributeFilter = $productAttributeFilter;
        $this->attributeRepository = $attributeRepository;
        $this->localeRepository = $localeRepository;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * {@inheritdoc}
     */
    public function filter(array $standardProduct): array
    {
        if (array_key_exists('values', $standardProduct) && is_array($standardProduct['values'])) {
            foreach ($standardProduct['values'] as $attributeCode => $values) {
                $this->checkGrantedAttribute($attributeCode);

                if (is_array($values)) {
                    foreach ($values as $value) {
                        $this->checkGrantedLocale($attributeCode, $value);
                    }
                }
            }
        }

        return $this->productAttributeFilter->filter($standardProduct);
    }

    /**
     * @param string $code
     *
     * @throws UnknownPropertyException
     */
    private function checkGrantedAttribute(string $code): void
    {
        $attribute = $this->attributeRepository->findOneByIdentifier($code);

        if (null === $attribute) {
            throw UnknownPropertyException::unknownProperty($code);
        }

        $group = $attribute->getGroup();
        if (!$this->authorizationChecker->isGranted(Attributes::VIEW_ATTRIBUTES, $group)) {
            throw UnknownPropertyException::unknownProperty($code);
        }
    }

    /**
     * @param string $attributeCode
     *
     * @throws UnknownPropertyException
     */
    private function checkGrantedLocale(string $attributeCode, array $value): void
    {
        if (!isset($value['locale'])) {
            return;
        }

        $locale = $this->localeRepository->findOneByIdentifier($value['locale']);

        if (null === $locale) {
            throw new UnknownPropertyException($value['locale'], sprintf(
                'Attribute "%s" expects an existing and activated locale, "%s" given.',
                $attributeCode,
                $value['locale']
            ));
        }

        if (!$this->authorizationChecker->isGranted(Attributes::VIEW_ITEMS, $locale)) {
            throw new UnknownPropertyException($value['locale'], sprintf(
                'Attribute "%s" expects an existing and activated locale, "%s" given.',
                $attributeCode,
                $value['locale']
            ));
        }
    }
}
