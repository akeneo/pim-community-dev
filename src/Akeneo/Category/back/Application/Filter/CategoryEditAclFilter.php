<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Filter;

use Oro\Bundle\SecurityBundle\SecurityFacade;

/**
 * Filters Std Format according to ACL rules.
 *
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryEditAclFilter
{
    private const ACLS = [
        'values' => 'pim_enrich_product_category_edit_attributes',
    ];

    public function __construct(private SecurityFacade $securityFacade)
    {
    }

    /**
     * @param array{
     *     code: string,
     *     labels: array<string, string>,
     *     values: array<string, array{
     *      data: string,
     *      locale: string|null,
     *      attribute_code: string
     *     }>
     * } $collection
     *
     * @return array<string, mixed>
     */
    public function filterCollection(array $collection): array
    {
        $newCategoryData = [];

        foreach ($collection as $type => $data) {
            if ($this->isAllowed($type)) {
                $newCategoryData[$type] = $data;
            }
        }

        return $newCategoryData;
    }

    private function isAllowed(string $type): bool
    {
        $isAllowed = true;

        if ($type == 'values') {
            $acl = $this->getAclForType($type);

            $isAllowed = null === $acl || $this->securityFacade->isGranted($acl);
        }

        return $isAllowed;
    }

    private function getAclForType(string $type): ?string
    {
        return self::ACLS[$type] ?? null;
    }
}
