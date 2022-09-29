<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\IdentifierGenerator\Acceptance\Context;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\CreateGeneratorCommand;
use Akeneo\Pim\Automation\IdentifierGenerator\Application\CreateGeneratorHandler;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\IdentifierGenerator;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\Property\FreeText;
use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Repository\IdentifierGeneratorRepository;
use Behat\Behat\Context\Context;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CreateIdentifierGeneratorContext implements Context
{
    public function __construct(
        private CreateGeneratorHandler $createGeneratorHandler,
        private IdentifierGeneratorRepository $generatorRepository,
    ) {
    }

    /**
     * @When I create an identifier generator
     */
    public function iCreateAnIdentifierGenerator(): void
    {
        ($this->createGeneratorHandler)(new CreateGeneratorCommand(
            'abcdef',
            [],
            [FreeText::fromString('abcdef')],
            ['fr' => 'Générateur'],
            'sku',
            '-'
        ));
    }

    /**
     * @Then The identifier generator is saved in the repository
     */
    public function identifierGeneratorIsSavedInTheRepository(): void
    {
        $identifierGenerator = $this->generatorRepository->get('abcdef');
        Assert::isInstanceOf($identifierGenerator, IdentifierGenerator::class);
    }
}
