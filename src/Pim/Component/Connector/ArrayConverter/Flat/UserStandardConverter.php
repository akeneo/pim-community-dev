<?php

namespace Pim\Component\Connector\ArrayConverter\Flat;

use Pim\Component\Connector\ArrayConverter\FieldsRequirementValidator;
use Pim\Component\Connector\ArrayConverter\StandardArrayConverterInterface;
use Pim\Component\Connector\Exception\ArrayConversionException;

/**
 * User Flat to Standard format Converter
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserStandardConverter implements StandardArrayConverterInterface
{
    /** @var FieldsRequirementValidator */
    protected $validator;

    /**
     * @param FieldsRequirementValidator $validator
     */
    public function __construct(FieldsRequirementValidator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * {@inheritdoc}
     *
     * Converts flat array to standard structured array:
     *
     * Before:
     * [
     *      'username'       => 'julia',
     *      'email'          => 'Julia@example.com',
     *      'password'       => 'julia',
     *      'first_name'     => 'Julia',
     *      'last_name'      => 'Stark',
     *      'catalog_locale' => 'en_US',
     *      'user_locale'    => 'en_US',
     *      'catalog_scope'  => 'ecommerce',
     *      'default_tree'   => 'men_2013',
     *      'roles'          => 'ROLE_USER',
     *      'groups'         => 'Redactor',
     *      'enabled'        => '1',
     * ]
     *
     * After:
     * [
     *      'username'       => 'julia',
     *      'email'          => 'Julia@example.com',
     *      'password'       => 'julia',
     *      'first_name'     => 'Julia',
     *      'last_name'      => 'Stark',
     *      'catalog_locale' => 'en_US',
     *      'user_locale'    => 'en_US',
     *      'catalog_scope'  => 'ecommerce',
     *      'default_tree'   => 'men_2013',
     *      'roles'          => ['ROLE_USER'],
     *      'groups'         => ['Redactor'],
     *      'enabled'        => true,
     * ]
     */
    public function convert(array $item, array $options = [])
    {
        $this->validator->validateFields(
            $item,
            ['username', 'email', 'password', 'enabled', 'roles', 'first_name', 'last_name'],
            true
        );
        $this->validator->validateFields(
            $item,
            ['groups']
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
