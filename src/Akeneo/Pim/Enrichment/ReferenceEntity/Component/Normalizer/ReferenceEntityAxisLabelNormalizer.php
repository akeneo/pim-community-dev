<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\ReferenceEntity\Component\Normalizer;

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Enrichment\ReferenceEntity\Component\Normalizer;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\InternalApi\AxisValueLabelsNormalizer\AxisValueLabelsNormalizer;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Repository\AttributeRepositoryInterface;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\Record\FindRecordDetailsInterface;

/**
 * @author Julian Prud'homme <julian.prudhomme@akeneo.com>
 */
class ReferenceEntityAxisLabelNormalizer implements AxisValueLabelsNormalizer
{
    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    /** @var FindRecordDetailsInterface */
    private $findRecordDetails;

    public function __construct(AttributeRepositoryInterface $attributeRepository, FindRecordDetailsInterface $findRecordDetails)
    {
        $this->attributeRepository = $attributeRepository;
        $this->findRecordDetails = $findRecordDetails;
    }

    public function normalize(ValueInterface $value, string $locale): string
    {
        $attribute = $this->attributeRepository->findOneByIdentifier($value->getAttributeCode());
        $recordDetails = ($this->findRecordDetails)(ReferenceEntityIdentifier::fromString($attribute->getReferenceDataName()), $value->getData());

        return $recordDetails->labels->getLabel($locale) ?? '[' . (string) $recordDetails->code . ']';
    }

    public function supports(string $attributeType): bool
    {
        return AttributeTypes::REFERENCE_ENTITY_SIMPLE_SELECT === $attributeType;
    }
}
