<?php

namespace Pim\Component\Connector\ArrayConverter\Flat\Product\Converter;

use Pim\Component\Connector\ArrayConverter\Flat\Product\Splitter\FieldSplitter;

/**
 * Abstract converter
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractConverter implements ConverterInterface
{
    /** @var array */
    protected $supportedFieldType;

    /** @var FieldSplitter */
    protected $fieldSplitter;

    /**
     * @param FieldSplitter $fieldSplitter
     */
    public function __construct(FieldSplitter $fieldSplitter)
    {
        $this->fieldSplitter = $fieldSplitter;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsField($fieldType)
    {
        return in_array($fieldType, $this->supportedFieldType);
    }
}
