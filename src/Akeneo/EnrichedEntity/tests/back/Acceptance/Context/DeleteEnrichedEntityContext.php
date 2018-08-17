<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\tests\back\Acceptance\Context;

use Akeneo\EnrichedEntity\Application\EnrichedEntity\DeleteEnrichedEntity\DeleteEnrichedEntityCommand;
use Akeneo\EnrichedEntity\Application\EnrichedEntity\DeleteEnrichedEntity\DeleteEnrichedEntityHandler;
use Akeneo\EnrichedEntity\Domain\Model\EnrichedEntity\EnrichedEntityIdentifier;
use Akeneo\EnrichedEntity\Domain\Query\EnrichedEntity\EnrichedEntityExistsInterface;
use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
final class DeleteEnrichedEntityContext implements Context
{
    /** @var DeleteEnrichedEntityHandler */
    private $deleteEnrichedEntityHandler;

    /** @var EnrichedEntityExistsInterface */
    private $enrichedEntityExists;

    public function __construct(
        DeleteEnrichedEntityHandler $deleteEnrichedEntityHandler,
        EnrichedEntityExistsInterface $enrichedEntityExists
    ) {
        $this->deleteEnrichedEntityHandler = $deleteEnrichedEntityHandler;
        $this->enrichedEntityExists = $enrichedEntityExists;
    }

    /**
     * @When /^the user deletes the enriched entity "([^"]+)"$/
     */
    public function theUserDeletesEnrichedEntity(string $identifier)
    {
        $command = new DeleteEnrichedEntityCommand();
        $command->identifier = $identifier;

        ($this->deleteEnrichedEntityHandler)($command);
    }

    /**
     * @Then /^there should be no enriched entity "([^"]+)"$/
     */
    public function thereShouldBeNoEnrichedEntity(string $identifier)
    {
        Assert::assertFalse($this->enrichedEntityExists->withIdentifier(
            EnrichedEntityIdentifier::fromString($identifier)
        ));
    }
}
