<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class DuplicateOptions extends Constraint
{
    public const DUPLICATE_ATTRIBUTE_OPTIONS = '11928f5d-3349-4d17-b21d-5eacabe61e01';

    public string $message = 'pim_catalog.constraint.duplicate_options';
    public string $attributeCode;

    public function getRequiredOptions(): array
    {
        return ['attributeCode'];
    }

    public function getDefaultOption(): string
    {
        return 'attributeCode';
    }
}
