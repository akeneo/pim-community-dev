<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\EventSubscriber;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Pim\Bundle\CatalogBundle\Doctrine\ORM\Query;
use Pim\Component\Catalog\EntityWithFamily\Event\ParentHasBeenAddedToProduct;
use Pim\Component\Catalog\Model\VariantProductInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * When a product is converted to a variant product we need to update the database and change the object in the
 * doctrine unit of work.
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddParentAProductSubscriber implements EventSubscriberInterface
{
    /** @var Query\ConvertProductToVariantProduct */
    private $convertProductToVariantProduct;

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var array */
    private $variantProducts = [];

    /**
     * @param Query\ConvertProductToVariantProduct $convertProductToVariantProduct
     * @param EntityManagerInterface               $entityManager
     */
    public function __construct(
        Query\ConvertProductToVariantProduct $convertProductToVariantProduct,
        EntityManagerInterface $entityManager
    ) {
        $this->convertProductToVariantProduct = $convertProductToVariantProduct;
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ParentHasBeenAddedToProduct::EVENT_NAME => 'scheduleForUpdate'
        ];
    }

    /**
     * We add the converted product to unit of work and we keep their ids in memory because we will update
     * their product type during the onflush event
     *
     * @param ParentHasBeenAddedToProduct $event
     */
    public function scheduleForUpdate(ParentHasBeenAddedToProduct $event): void
    {
        $variantProduct = $event->convertedProduct();

        $this->entityManager->getUnitOfWork()->registerManaged(
            $variantProduct,
            ['id' => $variantProduct->getId()],
            [
                'id' => $variantProduct->getId(),
                'parent' => null,
                'familyVariant' => null,
                'identifier' => $variantProduct->getIdentifier(),
                'groups' => $variantProduct->getGroups(),
                'associations' => $variantProduct->getAssociations(),
                'enabled' => $variantProduct->isEnabled(),
                'completenesses' => $variantProduct->getCompletenesses(),
                'family' => $variantProduct->getFamily(),
                'categories' => $variantProduct->getCategoriesForVariation(),
                'created' => $variantProduct->getCreated(),
                'updated' => $variantProduct->getUpdated(),
                'rawValues' => [],
                'uniqueData' => $variantProduct->getUniqueData(),
            ]
        );


        $this->variantProducts[$variantProduct->getId()] = $variantProduct->getId();
    }

    /**
     * We need to update the product type when a product has been converted in a variant product
     * We chose "preUpdate" because we want to change the product type during a transaction
     *
     * @param LifecycleEventArgs $args
     */
    public function preUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        if (!$entity instanceof VariantProductInterface) {
            return;
        }

        if (!in_array($entity->getId(), $this->variantProducts) || 0 === count($this->variantProducts)) {
            return;
        }

        unset($this->variantProducts[$entity->getId()]);

        ($this->convertProductToVariantProduct)($entity);
    }
}
