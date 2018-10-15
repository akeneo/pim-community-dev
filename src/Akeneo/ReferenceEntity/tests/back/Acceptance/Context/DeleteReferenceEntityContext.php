<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Acceptance\Context;

use Akeneo\ReferenceEntity\Application\ReferenceEntity\DeleteReferenceEntity\DeleteReferenceEntityCommand;
use Akeneo\ReferenceEntity\Application\ReferenceEntity\DeleteReferenceEntity\DeleteReferenceEntityHandler;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\ReferenceEntityExistsInterface;
use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
final class DeleteReferenceEntityContext implements Context
{
    /** @var DeleteReferenceEntityHandler */
    private $deleteReferenceEntityHandler;

    /** @var ReferenceEntityExistsInterface */
    private $referenceEntityExists;

    public function __construct(
        DeleteReferenceEntityHandler $deleteReferenceEntityHandler,
        ReferenceEntityExistsInterface $referenceEntityExists
    ) {
        $this->deleteReferenceEntityHandler = $deleteReferenceEntityHandler;
        $this->referenceEntityExists = $referenceEntityExists;
    }

    /**
     * @When /^the user deletes the reference entity "([^"]+)"$/
     */
    public function theUserDeletesReferenceEntity(string $identifier)
    {
        $command = new DeleteReferenceEntityCommand();
        $command->identifier = $identifier;

        ($this->deleteReferenceEntityHandler)($command);
    }

    /**
     * @Then /^there should be no reference entity "([^"]+)"$/
     */
    public function thereShouldBeNoReferenceEntity(string $identifier)
    {
        Assert::assertFalse($this->referenceEntityExists->withIdentifier(
            ReferenceEntityIdentifier::fromString($identifier)
        ));
    }
}
