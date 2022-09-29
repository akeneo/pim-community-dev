<?php

declare(strict_types=1);

namespace Specification\Akeneo\Pim\Automation\IdentifierGenerator\Infrastructure\Repository;

use Akeneo\Onboarder\Domain\CatalogStructure\Family\Query\GetRequiredAndNiceToHaveAttributeCodesForFamily;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\Conditions;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Delimiter;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGenerator;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGeneratorCode;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\LabelCollection;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\FreeText;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Structure;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Target;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\IdentifierGeneratorRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationRepository;
use Akeneo\Pim\TableAttribute\Infrastructure\TableConfiguration\Repository\LruCachedTableConfigurationRepository;
use PhpSpec\ObjectBehavior;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemoryIdentifierGeneratorRepositorySpec extends ObjectBehavior
{
    function it_is_an_identifier_generator_repository(): void
    {
        $this->shouldImplement(IdentifierGeneratorRepository::class);
    }

    function it_can_save_identifier_generators()
    {
        $identifierGenerator = new IdentifierGenerator(
            IdentifierGeneratorCode::fromString('abcdef'),
            Conditions::fromArray([]),
            Structure::fromArray([FreeText::fromString('abc')]),
            LabelCollection::fromNormalized(['fr' => 'Générateur']),
            Target::fromString('sku'),
            Delimiter::fromString('-'),
        );

        $this->save($identifierGenerator);

        $this->generators->shouldEqual([
            'abcdef' => $identifierGenerator,
        ]);

        $identifierGenerator2 = new IdentifierGenerator(
            IdentifierGeneratorCode::fromString('fedcba'),
            Conditions::fromArray([]),
            Structure::fromArray([FreeText::fromString('abc')]),
            LabelCollection::fromNormalized(['fr' => 'Générateur']),
            Target::fromString('sku'),
            Delimiter::fromString('-'),
        );

        $this->save($identifierGenerator2);
        $this->generators->shouldEqual([
            'abcdef' => $identifierGenerator,
            'fedcba' => $identifierGenerator2,
        ]);
    }

    function it_can_retrieve_an_identifier_generator_with_its_code()
    {
        $identifierGenerator = new IdentifierGenerator(
            IdentifierGeneratorCode::fromString('aabbcc'),
            Conditions::fromArray([]),
            Structure::fromArray([FreeText::fromString('abc')]),
            LabelCollection::fromNormalized(['fr' => 'Générateur']),
            Target::fromString('sku'),
            Delimiter::fromString('-'),
        );
        $this->save($identifierGenerator);

        $this->get('aabbcc')->shouldBeLike($identifierGenerator);
    }

    function it_returns_null_if_identifier_generator_is_not_found()
    {
        $this->get('unknown')->shouldReturn(null);
    }
}
