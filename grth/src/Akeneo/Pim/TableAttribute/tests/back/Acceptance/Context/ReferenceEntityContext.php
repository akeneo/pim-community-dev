<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Test\Pim\TableAttribute\Acceptance\Context;

use Akeneo\Platform\Bundle\FeatureFlagBundle\Internal\Test\InMemoryFeatureFlags;
use Akeneo\ReferenceEntity\Application\Record\CreateRecord\CreateRecordCommand;
use Akeneo\ReferenceEntity\Application\Record\CreateRecord\CreateRecordHandler;
use Akeneo\ReferenceEntity\Application\ReferenceEntity\CreateReferenceEntity\CreateReferenceEntityCommand;
use Akeneo\ReferenceEntity\Application\ReferenceEntity\CreateReferenceEntity\CreateReferenceEntityHandler;
use Akeneo\ReferenceEntity\Application\ReferenceEntity\DeleteReferenceEntity\DeleteReferenceEntityCommand;
use Akeneo\ReferenceEntity\Application\ReferenceEntity\DeleteReferenceEntity\DeleteReferenceEntityHandler;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Query\ReferenceEntity\ReferenceEntityExistsInterface;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Webmozart\Assert\Assert;

class ReferenceEntityContext implements Context
{
    public function __construct(
        private ValidatorInterface $validator,
        private ConstraintViolationsContext $constraintViolationsContext,
        private CreateReferenceEntityHandler $createReferenceEntityHandler,
        private DeleteReferenceEntityHandler $deleteReferenceEntityHandler,
        private ReferenceEntityExistsInterface $referenceEntityExists,
        private CreateRecordHandler $createRecordHandler,
        private InMemoryFeatureFlags $featureFlags
    ) {
    }

    /**
     * @BeforeScenario @reference-entity-feature-enabled
     */
    public function enabledReferenceEntityFeatureFlag()
    {
        $this->featureFlags->enable('reference_entity');
    }

    /**
     * @Given the :identifier reference entity
     */
    public function theReferenceEntity(string $identifier): void
    {
        $createCommand = new CreateReferenceEntityCommand($identifier, []);

        $violations = $this->validator->validate($createCommand);
        if ($violations->count() > 0) {
            throw new \LogicException(sprintf('Cannot create reference entity: %s', $violations->get(0)->getMessage()));
        }

        ($this->createReferenceEntityHandler)($createCommand);
    }

    /**
     * @When I delete the :identifier reference entity
     */
    public function iDeleteTheReferenceEntity(string $identifier): void
    {
        $deleteCommand = new DeleteReferenceEntityCommand($identifier);

        $violations = $this->validator->validate($deleteCommand);
        if ($violations->count() > 0) {
            $this->constraintViolationsContext->add($violations);

            return;
        }

        ($this->deleteReferenceEntityHandler)($deleteCommand);
    }

    /**
     * @Then The :identifier reference entity was deleted
     */
    public function theReferenceEntityWasDeleted(string $identifier): void
    {
        Assert::false(
            $this->referenceEntityExists->withIdentifier(ReferenceEntityIdentifier::fromString($identifier)),
            \sprintf('the %s reference entity was not deleted.', $identifier)
        );
    }

    /**
     * @Then The :identifier reference entity was not deleted
     */
    public function theReferenceEntityWasNotDeleted(string $identifier): void
    {
        Assert::true(
            $this->referenceEntityExists->withIdentifier(ReferenceEntityIdentifier::fromString($identifier)),
            \sprintf('the %s reference entity was deleted.', $identifier)
        );
    }

    /**
     * @Given /^the following records?:$/
     */
    public function theFollowingRecords(TableNode $records): void
    {
        foreach ($records as $normalizedRecord) {
            $createRecordCommand = new CreateRecordCommand(
                $normalizedRecord['ref entity'],
                $normalizedRecord['code'],
                []
            );

            $violations = $this->validator->validate($createRecordCommand);

            Assert::count($violations, 0, (string) $violations);
            $this->createRecordHandler->__invoke($createRecordCommand);
        }
    }
}
