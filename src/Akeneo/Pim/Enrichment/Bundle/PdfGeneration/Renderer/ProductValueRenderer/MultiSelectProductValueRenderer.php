<?php

namespace Akeneo\Pim\Enrichment\Bundle\PdfGeneration\Renderer\ProductValueRenderer;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\OptionsValue;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Twig\Environment;

class MultiSelectProductValueRenderer implements ProductValueRenderer
{
    private IdentifiableObjectRepositoryInterface $attributeOptionRepository;

    public function __construct(IdentifiableObjectRepositoryInterface $attributeOptionRepository)
    {
        $this->attributeOptionRepository = $attributeOptionRepository;
    }

    public function render(Environment $environment, AttributeInterface $attribute, ?ValueInterface $value, string $localeCode): ?string
    {
        if (!$value instanceof OptionsValue) {
            return null;
        }

        $optionCodes = $value->getData();

        return join(', ', array_map(fn ($optionCode): string => $this->getOptionLabel($attribute, $optionCode, $localeCode), $optionCodes));
    }

    public function supportsAttributeType(string $attributeType): bool
    {
        return $attributeType === AttributeTypes::OPTION_MULTI_SELECT;
    }

    private function getOptionLabel(AttributeInterface $attribute, string $optionCode, string $localeCode): string
    {
        $option = $this->attributeOptionRepository->findOneByIdentifier($attribute->getCode() . '.' . $optionCode);

        if (null === $option) {
            return sprintf('[%s]', $optionCode);
        }

        $option->setLocale($localeCode);
        $translation = $option->getTranslation();

        return null !== $translation->getValue() ? $translation->getValue() : sprintf('[%s]', $option->getCode());
    }
}
