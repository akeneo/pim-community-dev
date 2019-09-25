<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Storage\Indexer;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ProductModelIndexerInterface
{
    /**
     * @param string $productModelCode
     * @param array  $options
     */
    public function indexFromProductModelCode(string $productModelCode, array $options = []): void;

    /**
     * @param string[] $productModelCodes
     * @param array    $options
     */
    public function indexFromProductModelCodes(array $productModelCodes, array $options = []): void;

    /**
     * @param int   $productModelId
     * @param array $options
     */
    public function removeFromProductModelId(int $productModelId, array $options = []): void;

    /**
     * @param int[] $productModelIds
     * @param array $options
     */
    public function removeFromProductModelIds(array $productModelIds, array $options = []): void;
}
