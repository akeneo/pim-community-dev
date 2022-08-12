<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Persistence;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FindOneAttributeByCodeQueryInterface
{
    /**
     * @return array{code: string, label: string, type: string, scopable: bool, localizable: bool}
     */
    public function execute(string $code): ?array;
}
