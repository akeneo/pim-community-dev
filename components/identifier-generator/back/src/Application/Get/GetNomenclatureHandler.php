<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Application\Get;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\NomenclatureDefinition;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\NomenclatureRepository;
use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetNomenclatureHandler
{
    public function __construct(
        private readonly NomenclatureRepository $nomenclatureRepository,
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
    public function __invoke(GetNomenclatureCommand $command): array
    {
        $nomenclature = $this->nomenclatureRepository->get($command->propertyCode()) ?? new NomenclatureDefinition();

        Assert::allNotNull($nomenclature->values());

        return [
            'operator' => $nomenclature->operator(),
            'value' => $nomenclature->value(),
            'generate_if_empty' => $nomenclature->generateIfEmpty(),
            'values' => $nomenclature->values(),
        ];
    }
}
