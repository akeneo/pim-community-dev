<?php

namespace Pim\Component\Catalog\Builder;

use Pim\Component\Catalog\Association\MissingAssociationAdder;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\EntityWithAssociationsInterface;
use Pim\Component\Catalog\Model\EntityWithValuesInterface;
use Pim\Component\Catalog\ProductEvents;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\FamilyRepositoryInterface;
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

    /** @var MissingAssociationAdder */
    protected $missingAssociationAdder;

    /**
     * @param AttributeRepositoryInterface       $attributeRepository Attribute repository
     * @param FamilyRepositoryInterface          $familyRepository Family repository
     * @param EventDispatcherInterface           $eventDispatcher Event dispatcher
     * @param EntityWithValuesBuilderInterface   $entityWithValuesBuilder
     * @param MissingAssociationAdder            $missingAssociationAdder
     * @param array                              $classes Model classes
     *
     * @todo @merge Remove unused parameter $valuesResolver in master
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        FamilyRepositoryInterface $familyRepository,
        EventDispatcherInterface $eventDispatcher,
        EntityWithValuesBuilderInterface $entityWithValuesBuilder,
        MissingAssociationAdder $missingAssociationAdder,
        array $classes
    ) {
        $this->attributeRepository     = $attributeRepository;
        $this->familyRepository        = $familyRepository;
        $this->eventDispatcher         = $eventDispatcher;
        $this->productClass            = $classes['product'];
        $this->entityWithValuesBuilder = $entityWithValuesBuilder;
        $this->missingAssociationAdder = $missingAssociationAdder;
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
    public function addMissingAssociations(EntityWithAssociationsInterface $entity)
    {
        $this->missingAssociationAdder->addMissingAssociations($entity);

        return $this;
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
        EntityWithValuesInterface $values,
        AttributeInterface $attribute,
        $locale,
        $scope,
        $data
    ) {
        $this->entityWithValuesBuilder->addOrReplaceValue($values, $attribute, $locale, $scope, $data);
    }
}
