<?php

namespace Pim\Component\Connector\ArrayConverter\FlatToStandard;

use Pim\Component\Connector\ArrayConverter\ArrayConverterInterface;
use Pim\Component\Connector\ArrayConverter\FieldsRequirementChecker;

/**
 * Locale Flat to Standard format Converter
 *
 * @author    Clement Gautier <clement.gautier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Locale implements ArrayConverterInterface
{
    /** @var FieldsRequirementChecker */
    protected $checker;

    /**
     * @param FieldsRequirementChecker $checker
     */
    public function __construct(FieldsRequirementChecker $checker)
    {
        $this->checker = $checker;
    }

    /**
     * {@inheritdoc}
     *
     * Converts flat csv array to standard structured array:
     *
     * Before:
     * [
     *      'code' => 'en_US',
     * ]
     *
     * After:
     * [
     *      'code' => 'en_US',
     * ]
     */
    public function convert(array $item, array $options = [])
    {
        $this->checker->checkFieldsPresence($item, ['code']);
        $this->checker->checkFieldsFilling($item, ['code']);

        return ['code' => $item['code']];
    }
}
