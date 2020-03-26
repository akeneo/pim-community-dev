<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Updater\Clearer\Field;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Updater\Clearer\FieldClearerInterface;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GroupFieldClearer implements FieldClearerInterface
{
    private const SUPPORTED_FIELD = 'groups';

    /**
     * {@inheritDoc}
     */
    public function supportsField(string $field): bool
    {
        return static::SUPPORTED_FIELD === $field;
    }

    /**
     * {@inheritDoc}
     */
    public function clear($entity, string $field, array $options = []): void
    {
        if (!$this->supportsField($field)) {
            throw new \InvalidArgumentException(
                sprintf('Field must be "%s", "%s" given', static::SUPPORTED_FIELD, $field)
            );
        }

        // Groups are only available for products, not product models.
        if ($entity instanceof ProductInterface) {
            $entity->getGroups()->clear();
        }
    }
}
