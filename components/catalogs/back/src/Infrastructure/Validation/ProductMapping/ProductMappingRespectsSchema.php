<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Validation\ProductMapping;

use Symfony\Component\Validator\Constraint;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class ProductMappingRespectsSchema extends Constraint
{
    public string $productMappingSchemaFile;

    /**
     * {@inheritdoc}
     */
    public function __construct(string $productMappingSchemaFile, $options = null, array $groups = null, $payload = null)
    {
        parent::__construct($options, $groups, $payload);

        $this->productMappingSchemaFile = $productMappingSchemaFile;
    }
}
