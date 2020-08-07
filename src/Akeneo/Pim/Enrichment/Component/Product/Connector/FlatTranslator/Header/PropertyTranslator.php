<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\Header;

use Akeneo\Pim\Enrichment\Component\Product\Connector\FlatTranslator\FlatTranslatorInterface;
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

    public function translate(string $columnName, string $locale): string
    {
        return $this->labelTranslator->translate(
            sprintf('pim_common.%s', $columnName),
            $locale,
            sprintf(FlatTranslatorInterface::FALLBACK_PATTERN, $columnName)
        );
    }
}
