<?php

namespace Pim\Component\Catalog\Builder;

use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Manager\AttributeValuesResolverInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\EntityWithValuesInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\ProductEvents;
use Pim\Component\Catalog\Repository\AssociationTypeRepositoryInterface;
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

    /** @var AssociationTypeRepositoryInterface */
    protected $assocTypeRepository;

    /** @var EventDispatcherInterface */
    protected $eventDispatcher;

    /** @var AttributeValuesResolverInterface */
    protected $valuesResolver;

    /** @var string */
    protected $productClass;

    /** @var string */
    protected $associationClass;

    /**
     * @param AttributeRepositoryInterface       $attributeRepository Attribute repository
     * @param FamilyRepositoryInterface          $familyRepository    Family repository
     * @param AssociationTypeRepositoryInterface $assocTypeRepository Association type repository
     * @param EventDispatcherInterface           $eventDispatcher     Event dispatcher
     * @param AttributeValuesResolverInterface   $valuesResolver      Attributes values resolver
     * @param EntityWithValuesBuilderInterface   $entityWithValuesBuilder
     * @param array                              $classes             Model classes
     */
    public function __construct(
        AttributeRepositoryInterface $attributeRepository,
        FamilyRepositoryInterface $familyRepository,
        AssociationTypeRepositoryInterface $assocTypeRepository,
        EventDispatcherInterface $eventDispatcher,
        AttributeValuesResolverInterface $valuesResolver,
        EntityWithValuesBuilderInterface $entityWithValuesBuilder,
        array $classes
    ) {
        $this->attributeRepository     = $attributeRepository;
        $this->familyRepository        = $familyRepository;
        $this->assocTypeRepository     = $assocTypeRepository;
        $this->eventDispatcher         = $eventDispatcher;
        $this->valuesResolver          = $valuesResolver;
        $this->productClass            = $classes['product'];
        $this->associationClass        = $classes['association'];
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
            $this->addBooleanToProduct($product);
        }

        $event = new GenericEvent($product);
        $this->eventDispatcher->dispatch(ProductEvents::CREATE, $event);

        return $product;
    }

    /**
     * {@inheritdoc}
     */
    public function addMissingAssociations(ProductInterface $product)
    {
        $missingAssocTypes = $this->assocTypeRepository->findMissingAssociationTypes($product);
        if (!empty($missingAssocTypes)) {
            foreach ($missingAssocTypes as $associationType) {
                $association = new $this->associationClass();
                $association->setAssociationType($associationType);
                $product->addAssociation($association);
            }
        }

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

    /**
     * Set product values to "false" by default for every boolean attributes in the product's family.
     *
     * This workaround is due to the UI that does not manage null values for boolean attributes, only false or true.
     * It avoids to automatically submit boolean attributes belonging to the product's family in a proposal,
     * even if those boolean attributes were not modified by the user.
     *
     * FIXME : To remove when the UI will manage null values in boolean attributes (PIM-6056).
     *
     * @param ProductInterface $product
     */
    private function addBooleanToProduct(ProductInterface $product)
    {
        $family = $product->getFamily();

        if (null === $family) {
            return;
        }

        foreach ($family->getAttributes() as $attribute) {
            if (AttributeTypes::BOOLEAN === $attribute->getType()) {
                $requiredValues = $this->valuesResolver->resolveEligibleValues([$attribute]);

                foreach ($requiredValues as $value) {
                    $this->addOrReplaceValue($product, $attribute, $value['locale'], $value['scope'], false);
                }
            }
        }
    }
}
