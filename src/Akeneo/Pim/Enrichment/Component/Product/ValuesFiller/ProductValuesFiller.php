<?php

namespace Akeneo\Pim\Enrichment\Component\Product\ValuesFiller;

use Akeneo\Channel\Component\Repository\CurrencyRepositoryInterface;
use Akeneo\Pim\Enrichment\Bundle\Sql\GetFamilyAttributeCodes;
use Akeneo\Pim\Enrichment\Bundle\Sql\LruArrayAttributeRepository;
use Akeneo\Pim\Enrichment\Component\Product\Builder\EntityWithValuesBuilderInterface;
use Akeneo\Pim\Enrichment\Component\Product\Manager\AttributeValuesResolverInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithFamilyInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;

/**
 * Values filler for Products.
 *
 * Their attributes come directly from the family, and values come directly from the entity.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ProductValuesFiller extends AbstractEntityWithFamilyValuesFiller
{
    /** @var GetFamilyAttributeCodes */
    private $getFamilyAttributeCodes;

    public function __construct(
        EntityWithValuesBuilderInterface $entityWithValuesBuilder,
        AttributeValuesResolverInterface $valuesResolver,
        CurrencyRepositoryInterface $currencyRepository,
        LruArrayAttributeRepository $attributeRepository,
        GetFamilyAttributeCodes $getFamilyAttributeCodes
    ) {
        parent::__construct($entityWithValuesBuilder, $valuesResolver, $currencyRepository, $attributeRepository);
        $this->getFamilyAttributeCodes = $getFamilyAttributeCodes;
    }

    /**
     * {@inheritdoc}
     */
    protected function checkEntityType(EntityWithFamilyInterface $entity): void
    {
        if (!$entity instanceof ProductInterface) {
            throw new \InvalidArgumentException(
                sprintf('%s expected, %s given', ProductInterface::class, get_class($entity))
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function getExpectedAttributes(EntityWithFamilyInterface $entity): array
    {
        $attributes = [];

        // TODO: remove this when optional attributes are gone
        foreach ($entity->getUsedAttributeCodes() as $productAttributeCode) {
            $attribute = $this->attributeRepository->findOneByIdentifier($productAttributeCode);
            if (null !== $attribute) {
                $attributes[$productAttributeCode] = $attribute;
            }
        }

        $family = $entity->getFamily();
        if (null !== $family) {
            $familyAttributes = $this->attributeRepository->findSeveralByIdentifiers(
                $this->getFamilyAttributeCodes->execute($family->getCode())
            );
            foreach ($familyAttributes as $attribute) {
                $attributes[$attribute->getCode()] = $attribute;
            }
        }

        return $attributes;
    }
}
