<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\Header;

use Akeneo\Tool\Component\Localization\LabelTranslatorInterface;

class PropertyTranslator implements FlatHeaderTranslatorInterface
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
        return in_array($columnName, ['categories', 'family_variant', 'enabled', 'family', 'parent', 'groups']);
    }

    public function warmup(array $columnNames, string $locale): void
    {
    }

    public function translate(string $columnName, string $locale)
    {
        return $this->labelTranslator->translate(
            sprintf('pim_common.%s', $columnName),
            $locale,
            sprintf('[%s]', $columnName)
        );
    }
}
