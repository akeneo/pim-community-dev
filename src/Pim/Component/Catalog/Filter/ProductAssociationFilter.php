<?php

namespace Pim\Component\Catalog\Filter;

use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Comparator\ComparatorRegistry;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Filter product's association to have only updated or new values
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductAssociationFilter implements ProductFilterInterface
{
    /** @staticvar string */
    const ASSOCIATIONS_FIELD = 'associations';

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var ComparatorRegistry */
    protected $comparatorRegistry;

    /**
     * @param NormalizerInterface $normalizer
     * @param ComparatorRegistry  $comparatorRegistry
     */
    public function __construct(NormalizerInterface $normalizer, ComparatorRegistry $comparatorRegistry)
    {
        $this->normalizer         = $normalizer;
        $this->comparatorRegistry = $comparatorRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function filter(ProductInterface $product, array $newValues)
    {
        $originalValues = $this->getOriginalProduct($product);

        $result = [];
        foreach ($newValues as $code => $associations) {
            if (self::ASSOCIATIONS_FIELD !== $code) {
                throw new \LogicException(sprintf('Only "%s" field can be compared.', self::ASSOCIATIONS_FIELD));
            }

            foreach ($associations as $type => $field) {
                foreach ($field as $key => $association) {
                    $data = $this->compareAssociation($originalValues, $association, $type, $key);

                    if (!empty($data)) {
                        $result[self::ASSOCIATIONS_FIELD][$type][$key] = $data;
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Compare product's association
     *
     * @param array  $originalValues original associations
     * @param array  $associations   product's associations
     * @param string $type           type of association (PACK, SUBSTITUTION, etc)
     * @param string $key            key of group (products or groups)
     *
     * @throws \LogicException
     *
     * @return array|null
     */
    protected function compareAssociation(array $originalValues, array $associations, $type, $key)
    {
        $comparator = $this->comparatorRegistry->getFieldComparator(self::ASSOCIATIONS_FIELD);
        $diff = $comparator->compare($associations, $this->getOriginalAssociation($originalValues, $type, $key));

        if (null !== $diff) {
            return $diff;
        }

        return null;
    }

    /**
     * @param array  $originalValues original associations
     * @param string $type           type of association (PACK, SUBSTITUTION, etc)
     * @param string $key            key of group (products or groups)
     *
     * @return array
     */
    protected function getOriginalAssociation(array $originalValues, $type, $key)
    {
        return !isset($originalValues[$type][$key]) ? [] : $originalValues[$type][$key];
    }


    /**
     * Normalize original product
     *
     * @param ProductInterface $product
     *
     * @return array
     */
    protected function getOriginalProduct(ProductInterface $product)
    {
        $originalProduct = $this->normalizer->normalize($product, 'json', ['only_associations' => true]);

        return isset($originalProduct[self::ASSOCIATIONS_FIELD]) ? $originalProduct[self::ASSOCIATIONS_FIELD] : [];
    }
}
