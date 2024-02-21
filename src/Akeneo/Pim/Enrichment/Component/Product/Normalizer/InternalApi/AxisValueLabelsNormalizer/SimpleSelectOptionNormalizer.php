<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\AxisValueLabelsNormalizer;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class SimpleSelectOptionNormalizer implements AxisValueLabelsNormalizer
{
    /** @var IdentifiableObjectRepositoryInterface */
    private $attributeOptionRepository;

    public function __construct(IdentifiableObjectRepositoryInterface $attributeOptionRepository)
    {
        $this->attributeOptionRepository = $attributeOptionRepository;
    }

    /**
     * @param ValueInterface $value
     * @param string         $locale
     *
     * @return string
     */
    public function normalize(ValueInterface $value, string $locale): string
    {
        $optionCode = $value->getData();
        $option = $this->attributeOptionRepository->findOneByIdentifier($value->getAttributeCode().'.'.$optionCode);
        $option->setLocale($locale);
        $label = $option->getTranslation()->getLabel();

        return (null === $label || '' === $label) ? '[' . $option->getCode() . ']' : $label;
    }

    public function supports(string $attributeType): bool
    {
        return AttributeTypes::OPTION_SIMPLE_SELECT === $attributeType;
    }
}
