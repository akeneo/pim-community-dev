<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Acceptance\Context;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Exception\CouldNotFindIdentifierGeneratorException;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\Conditions;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Condition\Enabled;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Delimiter;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGenerator;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGeneratorCode;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGeneratorId;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\LabelCollection;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\FreeText;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Structure;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Target;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\TextTransformation;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\IdentifierGeneratorRepository;
use Behat\Behat\Context\Context;
use Webmozart\Assert\Assert;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class DatabaseContext implements Context
{
    public function __construct(
        private readonly IdentifierGeneratorRepository $generatorRepository,
    ) {
    }

    /**
     * @Given the ':generatorCode' identifier generator
     */
    public function theIdentifierGenerator(string $generatorCode): void
    {
        $identifierGenerator = new IdentifierGenerator(
            IdentifierGeneratorId::fromString('2038e1c9-68ff-4833-b06f-01e42d206002'),
            IdentifierGeneratorCode::fromString($generatorCode),
            Conditions::fromArray([Enabled::fromBoolean(true)]),
            Structure::fromArray([FreeText::fromString('abc')]),
            LabelCollection::fromNormalized(['fr_FR' => 'Générateur']),
            Target::fromString('sku'),
            Delimiter::fromString('-'),
            TextTransformation::fromString('no'),
        );
        $this->generatorRepository->save($identifierGenerator);
    }

    /**
     * @Then the identifier generator should not be created
     */
    public function theIdentifierShouldNotBeCreated(): void
    {
        try {
            $this->generatorRepository->get(BaseCreateOrUpdateIdentifierGenerator::DEFAULT_IDENTIFIER_GENERATOR_CODE);

            throw new \InvalidArgumentException(
                \sprintf(
                    'An identifier generator with code "%s" was created.',
                    BaseCreateOrUpdateIdentifierGenerator::DEFAULT_IDENTIFIER_GENERATOR_CODE
                )
            );
        } catch (CouldNotFindIdentifierGeneratorException) {
        }
    }

    /**
     * @Then The identifier generator is saved in the repository
     */
    public function identifierGeneratorIsSavedInTheRepository(): void
    {
        $identifierGenerator = $this->generatorRepository->get(BaseCreateOrUpdateIdentifierGenerator::DEFAULT_IDENTIFIER_GENERATOR_CODE);
        Assert::isInstanceOf($identifierGenerator, IdentifierGenerator::class);
    }

    /**
     * @Then The identifier generator is updated in the repository
     */
    public function identifierGeneratorIsUpdatedInTheRepository(): void
    {
        $identifierGenerator = $this->generatorRepository->get(BaseCreateOrUpdateIdentifierGenerator::DEFAULT_IDENTIFIER_GENERATOR_CODE);
        Assert::eq($identifierGenerator->delimiter()->asString(), 'updatedGenerator');
    }

    /**
     * @Then The identifier generator is updated without label in the repository
     */
    public function identifierGeneratorIsUpdatedWithoutLabelInTheRepository(): void
    {
        $identifierGenerator = $this->generatorRepository->get(BaseCreateOrUpdateIdentifierGenerator::DEFAULT_IDENTIFIER_GENERATOR_CODE);
        Assert::eq($identifierGenerator->labelCollection()->normalize(), []);
    }

    /**
     * @Then The identifier generator is updated in the repository and delimiter is null
     */
    public function theIdentifierGeneratorIsUpdatedInTheRepositoryAndDelimiterIsNull(): void
    {
        $identifierGenerator = $this->generatorRepository->get(BaseCreateOrUpdateIdentifierGenerator::DEFAULT_IDENTIFIER_GENERATOR_CODE);
        Assert::null($identifierGenerator->delimiter()->asString());
    }

    /**
     * @Then The identifier generator is updated in the repository and text transformation is lowercase
     */
    public function theIdentifierGeneratorIsUpdatedInTheRepositoryAndTextTransformationIsLowercase(): void
    {
        $identifierGenerator = $this->generatorRepository->get(BaseCreateOrUpdateIdentifierGenerator::DEFAULT_IDENTIFIER_GENERATOR_CODE);
        Assert::eq($identifierGenerator->textTransformation()->normalize(), TextTransformation::LOWERCASE);
    }

    /**
     * @Then there should be no :localeCode label for the :generatorCode generator
     */
    public function thereShouldBeNoLabelForLocale(string $localeCode, string $generatorCode): void
    {
        $identifierGenerator = $this->generatorRepository->get($generatorCode);
        Assert::isInstanceOf($identifierGenerator, IdentifierGenerator::class);
        Assert::keyNotExists($identifierGenerator->labelCollection()->normalize(), $localeCode);
    }

    /**
     * @Then /^the identifier generators should be ordered as (?P<codes>(('.*')(, | and )?)+)$/
     */
    public function theIdentifierGeneratorsShouldBeOrderedAs(string $codes): void
    {
        $generators = $this->generatorRepository->getAll();
        $orderedCodes = \array_map(
            static fn (IdentifierGenerator $generator): string => $generator->code()->asString(),
            $generators
        );

        Assert::same($orderedCodes, CodesSplitter::split($codes), \sprintf(
            "Codes are not sorted as expected:\n- Value: %s\nExpected: %s",
            \json_encode($orderedCodes),
            \json_encode(CodesSplitter::split($codes))
        ));
    }
}
