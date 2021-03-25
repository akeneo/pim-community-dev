<?php
declare(strict_types=1);

namespace Akeneo\UserManagement\Component\Connector\ArrayConverter\StandardToFlat;

use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\ArrayConverter\FieldsRequirementChecker;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class Role implements ArrayConverterInterface
{
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
     *      ],
     * ]
     *
     * After:
     * [
     *      'role' => 'ROLE_ADMINISTRATOR',
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
                    $convertedItem[$property] = implode(',', array_map(
                        fn (array $privilege) => $privilege['id'],
                        $data
                    ));
                    break;
                default:
                    $convertedItem[$property] = (string) $data;
            }
        }

        return $convertedItem;
    }
}
