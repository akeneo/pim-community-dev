<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Application\Get;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\Exception\UndefinedAttributeException;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\NomenclatureDefinition;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\FamilyProperty;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\FamilyNomenclatureRepository;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\ReferenceEntityNomenclatureRepository;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\SimpleSelectNomenclatureRepository;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetNomenclatureHandler
{
    public function __construct(
        private readonly FamilyNomenclatureRepository $familyNomenclatureRepository,
        private readonly SimpleSelectNomenclatureRepository $simpleSelectNomenclatureRepository,
        private readonly GetAttributes $getAttributes,
        private readonly ReferenceEntityNomenclatureRepository $referenceEntityNomenclatureRepository,
    ) {
    }

    /**
     * @return array{
     *     operator: ?string,
     *     value: ?int,
     *     generate_if_empty: ?bool,
     *     values: array<string, string>
     * }
     */
    public function __invoke(GetNomenclatureQuery $query): array
    {
        if ($query->propertyCode() === FamilyProperty::TYPE) {
            $nomenclature = $this->familyNomenclatureRepository->get() ?? new NomenclatureDefinition();
        } else {
            $attribute = $this->getAttributes->forCode($query->propertyCode());

            if (null === $attribute) {
                throw new UndefinedAttributeException(
                    \sprintf('The "%s" attribute is not found', $query->propertyCode())
                );
            }
            if ($attribute->type() === AttributeTypes::OPTION_SIMPLE_SELECT) {
                $nomenclature = $this->simpleSelectNomenclatureRepository->get($query->propertyCode()) ?? new NomenclatureDefinition();
            } else {
                $nomenclature = $this->referenceEntityNomenclatureRepository->get($query->propertyCode()) ?? new NomenclatureDefinition();
            }
        }

        Assert::allNotNull($nomenclature->values());

        return [
            'operator' => $nomenclature->operator(),
            'value' => $nomenclature->value(),
            'generate_if_empty' => $nomenclature->generateIfEmpty(),
            'values' => $nomenclature->values(),
        ];
    }
}
