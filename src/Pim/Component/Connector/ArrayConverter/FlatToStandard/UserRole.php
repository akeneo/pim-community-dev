<?php

namespace Pim\Component\Connector\ArrayConverter\FlatToStandard;

use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Pim\Component\Connector\ArrayConverter\FieldsRequirementChecker;
use Pim\Component\Connector\Exception\ArrayConversionException;

/**
 * User role Flat to Standard format Converter
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserRole implements ArrayConverterInterface
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
        $this->fieldsChecker->checkFieldsPresence($item, ['role', 'label']);
        $this->fieldsChecker->checkFieldsFilling($item, ['role', 'label']);

        return $item;
    }
}
