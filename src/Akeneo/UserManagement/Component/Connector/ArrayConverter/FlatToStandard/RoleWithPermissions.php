<?php
declare(strict_types=1);

namespace Akeneo\UserManagement\Component\Connector\ArrayConverter\FlatToStandard;

use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\ArrayConverter\FieldsRequirementChecker;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class RoleWithPermissions implements ArrayConverterInterface
{
    private const FIELDS_PRESENCE = ['role'];

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
     *          action:pim_enrich_product_create,
     *          action:pim_enrich_product_index,
     *      ],
     * ]
     */
    public function convert(array $item, array $options = []): array
    {
        $this->fieldsRequirementChecker->checkFieldsPresence($item, self::FIELDS_PRESENCE);
        $this->fieldsRequirementChecker->checkFieldsFilling($item, self::FIELDS_PRESENCE);

        $convertedItem = [];
        foreach ($item as $property => $data) {
            switch ($property) {
                case 'permissions':
                    if ('' === $data) {
                        $convertedItem[$property] = [];
                    } else {
                        $convertedItem[$property] = \explode(',', $data);
                    }
                    break;
                default:
                    $convertedItem[$property] = (string) $data;
            }
        }

        return $convertedItem;
    }
}
