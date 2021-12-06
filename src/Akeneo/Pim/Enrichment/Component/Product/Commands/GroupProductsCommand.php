<?php


namespace Akeneo\Pim\Enrichment\Component\Product\Commands;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GroupProductsCommand
{
    private string $groupId;
    private array $productIds;

    /**
     * @return string
     */
    public function groupId(): string
    {
        return $this->groupId;
    }

    /**
     * @return array
     */
    public function productIds(): array
    {
        return $this->productIds;
    }

    public function __construct(string $groupId, array $uptodateProductIds)
    {
        $this->groupId = $groupId;
        $this->productIds = $uptodateProductIds;
    }
}
