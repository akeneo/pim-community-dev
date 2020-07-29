<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\HeaderFlatTranslator;

use Akeneo\Tool\Component\Localization\LabelTranslatorInterface;

class PropertyHeaderFlatTranslator implements HeaderFlatTranslatorInterface
{
    /**
     * @var LabelTranslatorInterface
     */
    private $labelTranslator;

    public function __construct(LabelTranslatorInterface $labelTranslator)
    {
        $this->labelTranslator = $labelTranslator;
    }

    public function supports(string $columnName): bool
    {
        return in_array($columnName, ['categories', 'enabled', 'family', 'parent', 'groups']);
    }

    public function translate(string $columnName, string $locale, HeaderTranslationContext $context)
    {
        return $this->labelTranslator->translate(
            sprintf('pim_common.%s', $columnName),
            $locale,
            sprintf('[%s]', $columnName)
        );
    }
}
