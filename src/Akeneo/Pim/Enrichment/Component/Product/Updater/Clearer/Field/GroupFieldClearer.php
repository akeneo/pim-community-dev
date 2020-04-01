<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Updater\Clearer\Field;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Clearer\ClearerInterface;
use Webmozart\Assert\Assert;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GroupFieldClearer implements ClearerInterface
{
    private const SUPPORTED_FIELD = 'groups';

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

        // Groups are only available for products, not product models.
        if ($entity instanceof ProductInterface) {
            $entity->getGroups()->clear();
        }
    }
}
