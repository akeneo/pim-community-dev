<?php

namespace Pim\Bundle\DataGridBundle\Extension\Formatter\Property\ProductValue;

use Pim\Component\Localization\Formatter\FormatterInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Number property, able to render number product attributes
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NumberProperty extends FieldProperty
{
    /** @var FormatterInterface */
    protected $formatter;

    /**
     * @param TranslatorInterface $translator
     * @param FormatterInterface  $formatter
     */
    public function __construct(TranslatorInterface $translator, FormatterInterface $formatter)
    {
        parent::__construct($translator);

        $this->formatter = $formatter;
    }

    /**
     * {@inheritdoc}
     */
    protected function convertValue($value)
    {
        $result = $this->getBackendData($value);
        
        return $this->formatter->format($result);
    }
}
