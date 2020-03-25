<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Updater\Clearer;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FieldClearerInterface extends ClearerInterface
{
    public function supportsField(string $field): bool;

    /**
     * Clears the field value of the entity.
     */
    public function clear($entity, string $field, array $options = []): void;
}
