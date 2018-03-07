<?php

namespace Pim\Component\Connector\ArrayConverter\FlatToStandard;

use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Pim\Component\Connector\ArrayConverter\FieldsRequirementChecker;

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
     * ]
     */
    public function convert(array $item, array $options = [])
    {
        $this->fieldsChecker->checkFieldsPresence(
            $item,
            ['username', 'email', 'password', 'enabled', 'roles', 'first_name', 'last_name', 'groups']
        );
        $this->fieldsChecker->checkFieldsFilling(
            $item,
            ['username', 'email', 'password', 'enabled', 'roles', 'first_name', 'last_name']
        );

        $convertedItem = [];
        foreach ($item as $field => $data) {
            $convertedItem = $this->convertField($convertedItem, $field, $data);
        }

        return $convertedItem;
    }

    /**
     * @param array  $convertedItem
     * @param string $field
     * @param mixed  $data
     *
     * @return array
     */
    protected function convertField(array $convertedItem, $field, $data)
    {
        if (in_array($field, ['roles', 'groups'])) {
            $convertedItem[$field] = '' !== $data ? explode(',', $data) : [];
        } elseif (in_array($field, ['enabled', 'email_notifications'])) {
            $convertedItem[$field] = '1' === $data ? true : false;
        } else {
            $convertedItem[$field] = $data;
        }

        return $convertedItem;
    }
}
