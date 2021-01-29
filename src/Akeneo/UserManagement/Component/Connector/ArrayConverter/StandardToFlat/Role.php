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
    private const FIELDS_PRESENCE = ['label'];

    protected FieldsRequirementChecker $fieldsRequirementChecker;

    public function __construct(FieldsRequirementChecker $fieldsRequirementChecker)
    {
        $this->fieldsRequirementChecker = $fieldsRequirementChecker;
    }

    /**
     * {@inheritdoc}
     *
     * Before:
     * [
     *      'label' => 'Administrators',
     *      'permissions' => ['product_create', 'product_list'],
     * ]
     *
     * After:
     * [
     *      'label' => 'Administrators',
     *      'permissions' => 'product_create,product_list',
     * ]
     */
    public function convert(array $item, array $options = []): array
    {
        $this->fieldsRequirementChecker->checkFieldsPresence($item, static::FIELDS_PRESENCE);

        $convertedItem = [];
        foreach ($item as $property => $data) {
            switch ($property) {
                case 'permissions':
                    $convertedItem[$property] = implode(',', $data);
                    break;
                default:
                    $convertedItem[$property] = (string) $data;
            }
        }

        return $convertedItem;
    }
}
