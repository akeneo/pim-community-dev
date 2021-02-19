<?php
declare(strict_types=1);

namespace Akeneo\UserManagement\Component\Connector\ArrayConverter\FlatToStandard;

use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\ArrayConverter\FieldsRequirementChecker;
use Oro\Bundle\SecurityBundle\Acl\AccessLevel;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class Role implements ArrayConverterInterface
{
    private const ACL_EXTENSION_KEY = 'action';
    private const ACL_DEFAULT_PERMISSION = 'EXECUTE';
    private const FIELDS_PRESENCE = ['role', 'label'];

    private FieldsRequirementChecker $fieldsRequirementChecker;

    public function __construct(FieldsRequirementChecker $fieldsRequirementChecker)
    {
        $this->fieldsRequirementChecker = $fieldsRequirementChecker;
    }

    /**
     * {@inheritdoc}
     *
     * Before:
     * [
     *      'role' => 'ROLE_ADMINISTRATOR',
     *      'label' => 'Administrators',
     *      'permissions' => 'action:pim_enrich_product_create,action:pim_enrich_product_index',
     * ]
     *
     * After:
     * [
     *      'role' => 'ROLE_ADMINISTRATOR',
     *      'label' => 'Administrators',
     *      'permissions' => [
     *          [
     *              'id' => 'action:pim_enrich_product_create',
     *              'name' => 'pim_enrich_product_create',
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
     *              'type' => 'action',
     *              'permissions' => [
     *                  'EXECUTE' => [
     *                      'name' => 'EXECUTE',
     *                      'access_level' => 1,
     *                  ]
     *              ],
     *          ],
     *      ],
     * ]
     */
    public function convert(array $item, array $options = []): array
    {
        $this->fieldsRequirementChecker->checkFieldsPresence($item, static::FIELDS_PRESENCE);
        $this->fieldsRequirementChecker->checkFieldsFilling($item, static::FIELDS_PRESENCE);

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

        return array_merge(['permissions' => []], $convertedItem);
    }

    private function convertPermissions(string $data): array
    {
        $flatPermissionIds = explode(',', $data);

        $standardPermissions = [];
        foreach ($flatPermissionIds as $flatPermissionId) {
            $standardPermissions[] = [
                'id' => $flatPermissionId,
                'name' => false !== strpos($flatPermissionId, ':')
                    ? substr($flatPermissionId, strpos($flatPermissionId, ':') + 1)
                    : $flatPermissionId,
                'type' => static::ACL_EXTENSION_KEY,
                'permissions' => [static::ACL_DEFAULT_PERMISSION => [
                    'name' => static::ACL_DEFAULT_PERMISSION,
                    'access_level' => AccessLevel::BASIC_LEVEL,
                ]],
            ];
        }

        return $standardPermissions;
    }
}
