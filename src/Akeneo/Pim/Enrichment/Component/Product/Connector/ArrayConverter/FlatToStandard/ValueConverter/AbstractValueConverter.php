<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\ValueConverter;

use Akeneo\Pim\Enrichment\Component\Product\Connector\ArrayConverter\FlatToStandard\FieldSplitter;

/**
 * Abstract converter.
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractValueConverter implements ValueConverterInterface
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
    public function supportsField($attributeType)
    {
        return in_array($attributeType, $this->supportedFieldType);
    }
}
