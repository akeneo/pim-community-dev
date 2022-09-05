<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Builder;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithValuesInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\ProductEvents;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Structure\Component\Repository\FamilyRepositoryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Webmozart\Assert\Assert;

/**
 * Product builder
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductBuilder implements ProductBuilderInterface
{
    /** @var class-string */
    protected string $productClass;

    /**
     * @param array<string, class-string> $classes
     */
    public function __construct(
        protected AttributeRepositoryInterface $attributeRepository,
        protected FamilyRepositoryInterface $familyRepository,
        protected EventDispatcherInterface $eventDispatcher,
        protected EntityWithValuesBuilderInterface $entityWithValuesBuilder,
        array $classes
    ) {
        Assert::classExists($classes['product'] ?? '');
        Assert::implementsInterface($classes['product'], ProductInterface::class);
        $this->productClass = $classes['product'];
    }

    /**
     * {@inheritdoc}
     */
    public function createProduct(?string $identifier = null, ?string $familyCode = null, ?string $uuid = null): ProductInterface
    {
        $product = new $this->productClass($uuid);

        if (null !== $identifier) {
            $identifierAttribute = $this->attributeRepository->getIdentifier();
            $this->addOrReplaceValue($product, $identifierAttribute, null, null, $identifier);
        }

        if (null !== $familyCode) {
            $family = $this->familyRepository->findOneByIdentifier($familyCode);
            $product->setFamily($family);
        }

        $event = new GenericEvent($product);
        $this->eventDispatcher->dispatch($event, ProductEvents::CREATE);

        return $product;
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
    ): ?ValueInterface {
        return $this->entityWithValuesBuilder->addOrReplaceValue($entityWithValues, $attribute, $localeCode, $scopeCode, $data);
    }
}
