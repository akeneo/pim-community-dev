<?php

namespace Akeneo\Tool\Component\Connector\ArrayConverter;

/**
 * Dummy array converter.
 * It "converts" to the exact same array format but checks for fields presence & filling if asked.
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DummyConverter implements ArrayConverterInterface
{
    /** @var FieldsRequirementChecker */
    protected $checker;

    /** @var array */
    protected $fieldsPresence;

    /** @var array */
    protected $fieldsFilling;

    /**
     * @param FieldsRequirementChecker $checker
     * @param array                    $fieldsPresence
     * @param array                    $fieldsFilling
     */
    public function __construct(
        FieldsRequirementChecker $checker,
        array $fieldsPresence = [],
        array $fieldsFilling = []
    ) {
        $this->checker = $checker;
        $this->fieldsPresence = $fieldsPresence;
        $this->fieldsFilling = $fieldsFilling;
    }

    /**
     * {@inheritdoc}
     */
    public function convert(array $item, array $options = [])
    {
        if (!empty($this->fieldsPresence)) {
            $this->checker->checkFieldsPresence($item, $this->fieldsPresence);
        }
        if (!empty($this->fieldsFilling)) {
            $this->checker->checkFieldsFilling($item, $this->fieldsFilling);
        }

        return $item;
    }
}
