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
        'permissions' => 'pimee_enrich_category_edit_permissions',
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
        return $this->checkAclForType($type);
    }

    private function checkAclForType(string $type): bool
    {
        $acl = $this->getAclForType($type);

        return null === $acl || $this->securityFacade->isGranted($acl);
    }

    private function getAclForType(string $type): ?string
    {
        return self::ACLS[$type] ?? null;
    }
}
