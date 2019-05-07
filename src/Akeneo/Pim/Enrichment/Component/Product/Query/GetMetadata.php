<?php

declare(strict_types=1);

// TODO: move into Bundle
namespace Akeneo\Pim\Enrichment\Component\Product\Query;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @author    Mathias Métayer <mathias.metayer@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetMetadata implements GetMetadataInterface
{
    public function fromProductIdentifiers(int $userId, array $productIdentifiers): array
    {
        return [];
    }
}
