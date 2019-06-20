<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Acceptance\Context;

use Akeneo\ReferenceEntity\Application\ReferenceEntity\DeleteReferenceEntity\DeleteReferenceEntityCommand;
use Akeneo\ReferenceEntity\Application\ReferenceEntity\DeleteReferenceEntity\DeleteReferenceEntityHandler;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\ReferenceEntityExistsInterface;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityRepositoryInterface;
use Behat\Behat\Context\Context;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Webmozart\Assert\Assert;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
final class DeleteReferenceEntityContext implements Context
{
    /** @var ReferenceEntityRepositoryInterface  */
    private $referenceEntityRepository;

    /** @var DeleteReferenceEntityHandler */
    private $deleteReferenceEntityHandler;

    /** @var ReferenceEntityExistsInterface */
    private $referenceEntityExists;

    /** @var ValidatorInterface */
    private $validator;
    /** @var ConstraintViolationsContext */
    private $constraintViolationsContext;

    public function __construct(
        ReferenceEntityRepositoryInterface $referenceEntityRepository,
        DeleteReferenceEntityHandler $deleteReferenceEntityHandler,
        ReferenceEntityExistsInterface $referenceEntityExists,
        ValidatorInterface $validator,
        ConstraintViolationsContext $constraintViolationsContext
    ) {
        $this->referenceEntityRepository = $referenceEntityRepository;
        $this->deleteReferenceEntityHandler = $deleteReferenceEntityHandler;
        $this->referenceEntityExists = $referenceEntityExists;
        $this->constraintViolationsContext = $constraintViolationsContext;
        $this->validator = $validator;
    }

    /**
     * @When /^the user deletes the reference entity "([^"]+)"$/
     */
    public function theUserDeletesReferenceEntity(string $identifier)
    {
        $command = new DeleteReferenceEntityCommand($identifier);

        $this->constraintViolationsContext->addViolations($this->validator->validate($command));

        if (!$this->constraintViolationsContext->hasViolations()) {
            ($this->deleteReferenceEntityHandler)($command);
        }
    }

    /**
     * @Then /^there should be no reference entity "([^"]+)"$/
     */
    public function thereShouldBeNoReferenceEntity(string $identifier)
    {
        Assert::false($this->referenceEntityExists->withIdentifier(
            ReferenceEntityIdentifier::fromString($identifier)
        ));
    }
}
