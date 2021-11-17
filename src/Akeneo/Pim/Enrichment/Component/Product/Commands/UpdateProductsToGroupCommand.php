<?php


namespace Akeneo\Pim\Enrichment\Component\Product\Commands;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdateProductsToGroupCommand
{
    private string $groupId;
    private array $uptodateProductIds;

    /**
     * @return string
     */
    public function getGroupId(): string
    {
        return $this->groupId;
    }

    /**
     * @return array
     */
    public function getUptodateProductIds(): array
    {
        return $this->uptodateProductIds;
    }

    public function __construct(string $groupId, array $uptodateProductIds)
    {
        $this->groupId = $groupId;
        $this->uptodateProductIds = $uptodateProductIds;
    }
}
