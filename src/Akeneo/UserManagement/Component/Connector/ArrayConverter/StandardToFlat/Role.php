<?php
declare(strict_types=1);

namespace Akeneo\UserManagement\Component\Connector\ArrayConverter\StandardToFlat;

use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\ArrayConverter\FieldsRequirementChecker;
use Oro\Bundle\SecurityBundle\Acl\AccessLevel;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class Role implements ArrayConverterInterface
{
    private const FIELDS_PRESENCE = ['label'];

    private FieldsRequirementChecker $fieldsRequirementChecker;
    private AclManager $aclManager;

    public function __construct(FieldsRequirementChecker $fieldsRequirementChecker, AclManager $aclManager)
    {
        $this->fieldsRequirementChecker = $fieldsRequirementChecker;
        $this->aclManager = $aclManager;
    }

    /**
     * {@inheritdoc}
     *
     * Before:
     * [
     *      'label' => 'Administrators',
     *      'permissions' => [
     *          [
     *              'id' => 'action:pim_enrich_product_create',
     *              'name' => 'pim_enrich_product_create',
     *              'group' => 'pim_enrich.acl_group.product',
     *              'type' => 'action',
     *              'permissions' => [
     *                  'EXECUTE' => [
     *                      'name' => 'EXECUTE',
     *                      'access_level' => 1,
     *                  ]
     *              ],
     *          ],
     *          [
     *              'id' => 'action:pim_enrich_product_index',
     *              'name' => 'pim_enrich_product_index',
     *              'group' => 'pim_enrich.acl_group.product',
     *              'type' => 'action',
     *              'permissions' => [
     *                  'EXECUTE' => [
     *                      'name' => 'EXECUTE',
     *                      'access_level' => 1,
     *                  ]
     *              ],
     *          ],
     *          [
     *              'id' => 'action:pim_enrich_product_remove',
     *              'name' => 'pim_enrich_product_remove',
     *              'group' => 'pim_enrich.acl_group.product',
     *              'type' => 'action',
     *              'permissions' => [
     *                  'EXECUTE' => [
     *                      'name' => 'EXECUTE',
     *                      'access_level' => 0,
     *                  ]
     *              ],
     *          ],
     *      ],
     * ]
     *
     * After:
     * [
     *      'label' => 'Administrators',
     *      'permissions' => 'action:pim_enrich_product_create,action:pim_enrich_product_index',
     * ]
     */
    public function convert(array $item, array $options = []): array
    {
        $this->fieldsRequirementChecker->checkFieldsPresence($item, static::FIELDS_PRESENCE);

        $convertedItem = [];
        foreach ($item as $property => $data) {
            switch ($property) {
                case 'permissions':
                    $convertedItem[$property] = $this->convertPermissions($data);
                    break;
                default:
                    $convertedItem[$property] = (string) $data;
            }
        }

        return $convertedItem;
    }

    private function convertPermissions(array $normalizedPrivileges): string
    {
        $privilegeIds = [];
        foreach ($normalizedPrivileges as $privilege) {
            $allowedPermissionNames = $this->getAllowedPermissionNamesForType($privilege['type']);

            // For now all privileges have only one possible permission: "EXECUTE".
            // We cannot manage privilege with multiple permission in this export.
            if (count($allowedPermissionNames) > 1) {
                continue;
            }

            $allowedPermissionName = current($allowedPermissionNames);

            $permission = $privilege['permissions'][$allowedPermissionName] ?? null;
            if (null !== $permission && $permission['access_level'] !== AccessLevel::NONE_LEVEL) {
                $privilegeIds[] = $privilege['id'];
            }
        }

        return implode(',', $privilegeIds);
    }

    /**
     * @return string[]
     */
    private function getAllowedPermissionNamesForType(string $type): array
    {
        foreach ($this->aclManager->getAllExtensions() as $extension) {
            if ($extension->getExtensionKey() === $type) {
                return $extension->getPermissions();
            }
        }

        return [];
    }
}
