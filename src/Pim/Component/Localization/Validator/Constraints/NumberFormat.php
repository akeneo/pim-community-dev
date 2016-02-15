<?php

namespace Pim\Component\Localization\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Localized number constraint
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NumberFormat extends Constraint
{
    /** @var array */
    protected $separators = [
        '.' => 'point',
        ',' => 'comma',
        '٫' => 'arabic_decimal_separator',
    ];

    /** @var string */
    public $message = 'This type of value expects the use of {{ decimal_separator }} to separate decimals.';

    /** @var string */
    public $messageKey = 'decimal_separator';

    /** @var string */
    public $decimalSeparator;

    /** @var string */
    public $path;

    /**
     * Return the message parameters for constraint display.
     *
     * @param string|null $decimalSeparator
     *
     * @return array
     */
    public function getMessageParams($decimalSeparator = null)
    {
        if (null === $decimalSeparator) {
            $decimalSeparator = $this->decimalSeparator;
        }

        if (isset($this->separators[$decimalSeparator])) {
            $key = sprintf('%s.%s', $this->messageKey, $this->separators[$decimalSeparator]);
            return [
                'invalid_message'            => $key,
                'invalid_message_parameters' => []
            ];
        }

        return [
            'invalid_message'            => $this->message,
            'invalid_message_parameters' => ['{{ decimal_separator }}' => $decimalSeparator],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'pim_localization_number_format';
    }
}
