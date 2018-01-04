<?php
declare(strict_types=1);

namespace Pim\Component\Catalog\ProductModel\Filter;

use Akeneo\Component\StorageUtils\Exception\UnknownPropertyException;
use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\VariantProductInterface;
use Symfony\Component\OptionsResolver\Exception\MissingOptionsException;

/**
 * Filter data according to attributes defined on the family (for the products)
 * or on the family variant (variant product). All attributes that don't belong
 * to the corresponding family (product) or attribute set (variant product) will
 * be removed.
 *
 * This is because when variant products are exported, they gather the values
 * of their parent, and we should be able to import those export files.
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductAttributeFilter implements AttributeFilterInterface
{
    /** @var IdentifiableObjectRepositoryInterface */
    private $productModelRepository;

    /** @var IdentifiableObjectRepositoryInterface */
    private $familyRepository;

    /** @var IdentifiableObjectRepositoryInterface */
    private $productRepository;

    /** @var IdentifiableObjectRepositoryInterface */
    private $attributeRepository;

    /**
     * @param IdentifiableObjectRepositoryInterface $productModelRepository
     * @param IdentifiableObjectRepositoryInterface $familyRepository
     * @param IdentifiableObjectRepositoryInterface $productRepository
     * @param IdentifiableObjectRepositoryInterface $attributeRepository
     */
    public function __construct(
        IdentifiableObjectRepositoryInterface $productModelRepository,
        IdentifiableObjectRepositoryInterface $familyRepository,
        IdentifiableObjectRepositoryInterface $productRepository,
        IdentifiableObjectRepositoryInterface $attributeRepository
    ) {
        $this->productModelRepository = $productModelRepository;
        $this->familyRepository = $familyRepository;
        $this->productRepository = $productRepository;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function filter(array $standardProduct): array
    {
        if (!array_key_exists('identifier', $standardProduct)) {
            throw new MissingOptionsException('The "identifier" key is missing');
        }

        if (array_key_exists('values', $standardProduct) && is_array($standardProduct['values'])) {
            foreach ($standardProduct['values'] as $code => $value) {
                if (null === $this->attributeRepository->findOneByIdentifier($code)) {
                    throw UnknownPropertyException::unknownProperty($code);
                }
            }
        }

        $product = $this->productRepository->findOneByIdentifier($standardProduct['identifier']);
        if (null !== $product && $product instanceof VariantProductInterface && null !== $product->getParent()
            && !array_key_exists('parent', $standardProduct)) {
            $standardProduct['parent'] = $product->getParent()->getCode();
        }

        if (isset($standardProduct['parent']) &&
            null !== $parentProductModel = $this->productModelRepository->findOneByIdentifier($standardProduct['parent'])
        ) {
            $attributeSet = $parentProductModel
                ->getFamilyVariant()
                ->getVariantAttributeSet($parentProductModel->getVariationLevel() + 1);
            $attributes = new ArrayCollection(array_merge(
                $attributeSet->getAttributes()->toArray(),
                $attributeSet->getAxes()->toArray()
            ));

            return $this->keepOnlyAttributes($standardProduct, $attributes);
        }

        if (isset($standardProduct['family'])) {
            if (null !== $family = $this->familyRepository->findOneByIdentifier($standardProduct['family'])) {
                $attributes = $family->getAttributes();

                return $this->keepOnlyAttributes($standardProduct, $attributes);
            }
        }

        return $standardProduct;
    }

    /**
     * @param array      $flatProduct
     * @param Collection $attributesToKeep
     *
     * @return array
     */
    private function keepOnlyAttributes(array $flatProduct, Collection $attributesToKeep): array
    {
        $attributeCodesToKeep = $attributesToKeep->map(function (AttributeInterface $attribute) {
            return $attribute->getCode();
        })->toArray();

        foreach ($flatProduct['values'] as $attributeName => $value) {
            if (!in_array($attributeName, $attributeCodesToKeep)) {
                unset($flatProduct['values'][$attributeName]);
            }
        }

        return $flatProduct;
    }
}
