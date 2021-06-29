<?php

namespace Akeneo\UserManagement\Component\Connector\ArrayConverter\FlatToStandard;

use Akeneo\Tool\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Akeneo\Tool\Component\Connector\ArrayConverter\FieldsRequirementChecker;

/**
 * User Flat to Standard format Converter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class User implements ArrayConverterInterface
{
    /** @var FieldsRequirementChecker */
    protected $fieldsChecker;

    /**
     * @param FieldsRequirementChecker $fieldsChecker
     */
    public function __construct(FieldsRequirementChecker $fieldsChecker)
    {
        $this->fieldsChecker = $fieldsChecker;
    }

    /**
     * {@inheritdoc}
     *
     * Converts flat array to standard structured array:
     *
     * Before:
     * [
     *      'username'               => 'julia',
     *      'email'                  => 'Julia@example.com',
     *      'password'               => 'julia',
     *      'first_name'             => 'Julia',
     *      'last_name'              => 'Stark',
     *      'catalog_default_locale' => 'en_US',
     *      'user_default_locale'    => 'en_US',
     *      'catalog_default_scope'  => 'ecommerce',
     *      'default_category_tree'  => 'men_2013',
     *      'roles'                  => 'ROLE_USER',
     *      'groups'                 => 'Redactor',
     *      'enabled'                => '1',
     *      'timezone'               => 'UTC',
     * ]
     *
     * After:
     * [
     *      'username'               => 'julia',
     *      'email'                  => 'Julia@example.com',
     *      'password'               => 'julia',
     *      'first_name'             => 'Julia',
     *      'last_name'              => 'Stark',
     *      'catalog_default_locale' => 'en_US',
     *      'user_default_locale'    => 'en_US',
     *      'catalog_default_scope'  => 'ecommerce',
     *      'default_category_tree'  => 'men_2013',
     *      'roles'                  => ['ROLE_USER'],
     *      'groups'                 => ['Redactor'],
     *      'enabled'                => true,
     *      'timezone'               => 'UTC',
     * ]
     */
    public function convert(array $item, array $options = []): array
    {
        $this->fieldsChecker->checkFieldsPresence($item, ['username']);
        $this->fieldsChecker->checkFieldsFilling($item, ['username']);

        $convertedItem = [];
        foreach ($item as $field => $data) {
            $convertedItem = $this->convertField($convertedItem, $field, $data);
        }

        return $convertedItem;
    }

    /**
     * @param array $convertedItem
     * @param string $field
     * @param mixed $data
     *
     * @return array
     */
    protected function convertField(array $convertedItem, string $field, $data): array
    {
        if ('' === $data) {
            $convertedItem[$field] = null;

            return $convertedItem;
        }

        switch ($field) {
            case 'roles':
            case 'groups':
            case 'product_grid_filters':
                $convertedItem[$field] = \explode(',', $data);
                break;
            case 'enabled':
                $convertedItem[$field] = $this->convertBoolean((string)$data);
                break;
            case 'avatar':
                $convertedItem['avatar'] = ['filePath' => $data];
                break;
            default:
                $convertedItem[$field] = $data;
                break;
        }

        return $convertedItem;
    }

    private function convertBoolean(string $data)
    {
        if ('1' === $data) {
            return true;
        } elseif ('0' === $data) {
            return false;
        }

        return $data;
    }
}
