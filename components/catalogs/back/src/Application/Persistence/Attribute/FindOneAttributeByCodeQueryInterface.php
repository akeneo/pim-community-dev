<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Persistence\Attribute;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-type Attribute array{
 *      code: string,
 *      label: string,
 *      type: string,
 *      scopable: bool,
 *      localizable: bool,
 *      attribute_group_code: string,
 *      attribute_group_label: string,
 *      measurement_family?: string,
 *      default_measurement_unit?: string
 *      asset_family?: string
 * }
 */
interface FindOneAttributeByCodeQueryInterface
{
    /**
     * @return Attribute|null
     */
    public function execute(string $code): ?array;
}
