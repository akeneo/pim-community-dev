<?php

namespace Pim\Component\Connector\ArrayConverter\Flat;

use Pim\Component\Connector\ArrayConverter\FieldsRequirementValidator;
use Pim\Component\Connector\ArrayConverter\StandardArrayConverterInterface;
use Pim\Component\Connector\Exception\ArrayConversionException;

/**
 * User role Flat to Standard format Converter
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserRoleStandardConverter implements StandardArrayConverterInterface
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
     *      'role'  => 'ROLE_ADMINISTRATOR'
     *      'label' => 'Administrator'
     * ]
     *
     * After:
     * [
     *      'role'  => 'ROLE_ADMINISTRATOR',
     *      'label' => 'Administrator',
     * ]
     */
    public function convert(array $item, array $options = [])
    {
        $this->validator->validateFields($item, ['role', 'label'], true);

        return $item;
    }
}
