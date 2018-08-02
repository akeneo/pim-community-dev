<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Batch\Api\Product\Association;

/**
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Association
{
    /** @var string */
    private $associationType;

    /** @var string[] */
    private $groupCodes;

    /** @var string[] */
    private $productIdentifiers;

    /**
     * @param string   $associationType
     * @param string[] $groupCodes
     * @param string[] $productIdentifiers
     */
    public function __construct(string $associationType, array $groupCodes, array $productIdentifiers)
    {
        $this->associationType = $associationType;
        $this->groupCodes = $groupCodes;
        $this->productIdentifiers = $productIdentifiers;
    }

    public function associationType(): string
    {
        return $this->associationType;
    }

    public function groupCodes(): array
    {
        return $this->groupCodes;
    }

    public function productIdentifiers(): array
    {
        return $this->productIdentifiers;
    }

}
