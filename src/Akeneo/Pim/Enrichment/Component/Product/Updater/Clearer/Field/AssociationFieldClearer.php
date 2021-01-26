<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Updater\Clearer\Field;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithAssociationsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Clearer\ClearerInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\TwoWayAssociationUpdaterInterface;
use Akeneo\Tool\Component\StorageUtils\Exception\InvalidObjectException;
use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AssociationFieldClearer implements ClearerInterface
{
    private const SUPPORTED_FIELD = 'associations';

    private TwoWayAssociationUpdaterInterface $twoWayAssociationUpdater;

    public function __construct(TwoWayAssociationUpdaterInterface $twoWayAssociationUpdater)
    {
        $this->twoWayAssociationUpdater = $twoWayAssociationUpdater;
    }

    /**
     * {@inheritDoc}
     */
    public function supportsProperty(string $property): bool
    {
        return static::SUPPORTED_FIELD === $property;
    }

    /**
     * {@inheritDoc}
     */
    public function clear($entity, string $property, array $options = []): void
    {
        Assert::true(
            $this->supportsProperty($property),
            sprintf('The clearer does not handle the "%s" property.', $property)
        );

        if (!$entity instanceof EntityWithAssociationsInterface) {
            throw InvalidObjectException::objectExpected($entity, EntityWithAssociationsInterface::class);
        }

        foreach ($entity->getAssociations() as $association) {
            $associationType = $association->getAssociationType();
            $typeCode = $associationType->getCode();

            foreach ($entity->getAssociatedProducts($typeCode) as $associatedProduct) {
                $entity->removeAssociatedProduct($associatedProduct, $typeCode);
                if ($associationType->isTwoWay()) {
                    $this->twoWayAssociationUpdater->removeInversedAssociation(
                        $entity,
                        $typeCode,
                        $associatedProduct
                    );
                }
            }
            foreach ($entity->getAssociatedProductModels($typeCode) as $associatedProductModel) {
                $entity->removeAssociatedProductModel($associatedProductModel, $typeCode);
                if ($associationType->isTwoWay()) {
                    $this->twoWayAssociationUpdater->removeInversedAssociation(
                        $entity,
                        $typeCode,
                        $associatedProductModel
                    );
                }
            }
            foreach ($entity->getAssociatedGroups($typeCode) as $associatedGroup) {
                $entity->removeAssociatedGroup($associatedGroup, $typeCode);
            }
        }
    }
}
