<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Builder;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\ProductEvents;
use Akeneo\Pim\Enrichment\Component\Product\Value\ScalarValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Product builder
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductBuilder implements ProductBuilderInterface
{
    /** @var EntityWithValuesBuilderInterface */
    protected $entityWithValuesBuilder;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var FamilyRepositoryInterface */
    protected $familyRepository;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var string */
    protected $productClass;

    /**
     * @param AttributeRepositoryInterface       $attributeRepository Attribute repository
     * @param FamilyRepositoryInterface          $familyRepository Family repository
     * @param EventDispatcherInterface           $eventDispatcher Event dispatcher
     * @param EntityWithValuesBuilderInterface   $entityWithValuesBuilder
     * @param array                              $classes Model classes
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        FamilyRepositoryInterface $familyRepository,
        EventDispatcherInterface $eventDispatcher,
        EntityWithValuesBuilderInterface $entityWithValuesBuilder,
        array $classes
    ) {
        $this->attributeRepository     = $attributeRepository;
        $this->familyRepository        = $familyRepository;
        $this->eventDispatcher         = $eventDispatcher;
        $this->productClass            = $classes['product'];
        $this->entityWithValuesBuilder = $entityWithValuesBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function createProduct($identifier = null, $familyCode = null)
    {
        $product = new $this->productClass();

        if (null !== $identifier) {
            $identifierAttribute = $this->attributeRepository->getIdentifier();
            $this->addOrReplaceValue($product, $identifierAttribute, null, null, $identifier);
        }

        if (null !== $familyCode) {
            $family = $this->familyRepository->findOneByIdentifier($familyCode);
            $product->setFamily($family);
        }

        $event = new GenericEvent($product);
        $this->eventDispatcher->dispatch(ProductEvents::CREATE, $event);

        return $product;
    }

    /**
     * {@inheritdoc}
     */
    public function addAttribute(EntityWithValuesInterface $values, AttributeInterface $attribute)
    {
        $this->entityWithValuesBuilder->addAttribute($values, $attribute);
    }

    /**
     * {@inheritdoc}
     */
    public function addOrReplaceValue(
        EntityWithValuesInterface $entityWithValues,
        AttributeInterface $attribute,
        ?string $localeCode,
        ?string $scopeCode,
        $data
    ) :ValueInterface {
        // TODO: TIP-722: This is a temporary fix, Product identifier should be used only as a field
        if (AttributeTypes::IDENTIFIER === $attribute->getType() && null !== $data && $entityWithValues instanceof ProductInterface) {
            $identifierValue = ScalarValue::value($attribute->getCode(), $data);
            $entityWithValues->setIdentifier($identifierValue);

            return $identifierValue;
        }

        return $this->entityWithValuesBuilder->addOrReplaceValue($entityWithValues, $attribute, $localeCode, $scopeCode, $data);
    }
}
