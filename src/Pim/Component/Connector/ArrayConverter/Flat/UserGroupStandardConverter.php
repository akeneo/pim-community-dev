<?php

namespace Pim\Component\Connector\ArrayConverter\Flat;

use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Pim\Component\Connector\ArrayConverter\FieldsRequirementChecker;
use Pim\Component\Connector\Exception\ArrayConversionException;

/**
 * User group Flat to Standard format Converter
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UserGroupStandardConverter implements ArrayConverterInterface
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
     * Converts flat csv array to standard structured array:
     *
     * Before:
     * [
     *      'name' => 'IT support'
     * ]
     *
     * After:
     * [
     *      'name' => 'IT support',
     * ]
     */
    public function convert(array $item, array $options = [])
    {
        $this->fieldsChecker->checkFieldsPresence($item, ['name']);
        $this->fieldsChecker->checkFieldsFilling($item, ['name']);

        return $item;
    }
}
