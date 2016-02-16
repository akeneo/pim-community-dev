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
    protected $decimalSeparators;

    /** @var string */
    public $message = 'This type of value expects the use of {{ decimal_separator }} to separate decimals.';

    /** @var string */
    public $decimalSeparator;

    /** @var string */
    public $path;

    /**
     * @param array $decimalSeparators
     */
    public function __construct(array $decimalSeparators)
    {
        parent::__construct();

        $this->decimalSeparators = $decimalSeparators;
    }

    /**
     * Return the message translation key for constraint display.
     *
     * @return string
     */
    public function getMessageKey()
    {
        if (isset($this->decimalSeparators[$this->decimalSeparator])) {
            return str_replace(
                '{{ decimal_separator }}',
                $this->decimalSeparators[$this->decimalSeparator],
                $this->message
            );
        }

        return $this->message;
    }

    /**
     * Return the message translation params for constraint display.
     *
     * @return array
     */
    public function getMessageParams()
    {
        if (isset($this->decimalSeparators[$this->decimalSeparator])) {
            return [];
        }

        return ['{{ decimal_separator }}' => $this->decimalSeparator];
    }

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return 'pim_localization_number_format';
    }
}
