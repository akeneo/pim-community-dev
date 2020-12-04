<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Updater\Clearer\Field;

use Akeneo\Pim\Enrichment\Component\Product\Model\EntityWithAssociationsInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Clearer\ClearerInterface;
use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AssociationFieldClearer implements ClearerInterface
{
    private const SUPPORTED_FIELD = 'associations';

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

        if ($entity instanceof EntityWithAssociationsInterface) {
            // getAssociations() can return an array or a Collection. We handle both.
            // We cannot clear the association directly, doctrine does not understand. We have to clear the
            // products, product models and groups of each assocations.
            $associations = $entity->getAssociations();
            foreach ($associations as $association) {
                $association->getProducts()->clear();
                $association->getProductModels()->clear();
                $association->getGroups()->clear();
            }
            $entity->setAssociations($associations);
        }
    }
}
