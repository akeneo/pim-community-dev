<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Acceptance\Context;

use Akeneo\ReferenceEntity\Application\ReferenceEntity\EditReferenceEntity\EditReferenceEntityCommand;
use Akeneo\ReferenceEntity\Application\ReferenceEntity\EditReferenceEntity\EditReferenceEntityHandler;
use Akeneo\ReferenceEntity\Common\Fake\InMemoryFindActivatedLocalesByIdentifiers;
use Akeneo\ReferenceEntity\Domain\Model\Image;
use Akeneo\ReferenceEntity\Domain\Model\LocaleIdentifier;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntity;
use Akeneo\ReferenceEntity\Domain\Model\ReferenceEntity\ReferenceEntityIdentifier;
use Akeneo\ReferenceEntity\Domain\Repository\ReferenceEntityRepositoryInterface;
use Akeneo\Tool\Component\FileStorage\Model\FileInfo;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use PHPUnit\Framework\Assert;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
final class EditReferenceEntityContext implements Context
{
    /** @var ReferenceEntityRepositoryInterface */
    private $referenceEntityRepository;

    /** @var EditReferenceEntityHandler */
    private $editReferenceEntityHandler;

    /** @var ValidatorInterface */
    private $validator;

    /** @var ConstraintViolationListInterface */
    private $violations;

    /** @var ConstraintViolationsContext */
    private $constraintViolationsContext;

    /** @var InMemoryFindActivatedLocalesByIdentifiers */
    private $activatedLocales;

    /**
     * @param ReferenceEntityRepositoryInterface $referenceEntityRepository
     * @param EditReferenceEntityHandler         $editReferenceEntityHandler
     * @param ValidatorInterface                $validator
     * @param ConstraintViolationsContext       $constraintViolationsContext
     */
    public function __construct(
        ReferenceEntityRepositoryInterface $referenceEntityRepository,
        EditReferenceEntityHandler $editReferenceEntityHandler,
        ValidatorInterface $validator,
        ConstraintViolationsContext $constraintViolationsContext,
        InMemoryFindActivatedLocalesByIdentifiers $activatedLocales
    ) {
        $this->referenceEntityRepository = $referenceEntityRepository;
        $this->editReferenceEntityHandler = $editReferenceEntityHandler;
        $this->validator = $validator;
        $this->constraintViolationsContext = $constraintViolationsContext;
        $this->activatedLocales = $activatedLocales;
    }

    /**
     * @Given /^a reference entity$/
     * @Given /^a valid reference entity$/
     */
    public function theFollowingReferenceEntity()
    {
        $this->activatedLocales->save(LocaleIdentifier::fromCode('en_US'));
        $this->activatedLocales->save(LocaleIdentifier::fromCode('fr_FR'));

        $this->referenceEntityRepository->create(
            ReferenceEntity::create(
                ReferenceEntityIdentifier::fromString('designer'),
                [
                    'en_US' => 'Designer',
                    'fr_FR' => 'Concepteur'
                ],
                Image::createEmpty()
            )
        );
    }

    /**
     * @When /^the user updates the reference entity "([^"]*)" with:$/
     */
    public function theUserUpdatesTheReferenceEntityWith(string $identifier, TableNode $updateTable)
    {
        $updates = $updateTable->getRowsHash();
        $command = new EditReferenceEntityCommand();
        $command->identifier = $identifier;
        $command->labels = json_decode($updates['labels'], true);
        ($this->editReferenceEntityHandler)($command);
    }

    /**
     * @Then /^the reference entity "([^"]*)" should be:$/
     */
    public function theReferenceEntityShouldBe(string $identifier, TableNode $referenceEntityTable)
    {
        $expectedIdentifier = ReferenceEntityIdentifier::fromString($identifier);
        $expectedInformation = current($referenceEntityTable->getHash());
        $actualReferenceEntity = $this->referenceEntityRepository->getByIdentifier($expectedIdentifier);
        $this->assertSameLabels(
            json_decode($expectedInformation['labels'], true),
            $actualReferenceEntity
        );
    }

    private function assertSameLabels(array $expectedLabels, ReferenceEntity $actualReferenceEntity)
    {
        $actualLabels = [];
        foreach ($actualReferenceEntity->getLabelCodes() as $labelCode) {
            $actualLabels[$labelCode] = $actualReferenceEntity->getLabel($labelCode);
        }

        $differences = array_merge(
            array_diff($expectedLabels, $actualLabels),
            array_diff($actualLabels, $expectedLabels)
        );

        Assert::assertEmpty(
            $differences,
            sprintf('Expected labels "%s", but found %s', json_encode($expectedLabels), json_encode($actualLabels))
        );
    }

    /**
     * @Given /^the reference entity \'([^\']*)\' with the label \'([^\']*)\' equal to \'([^\']*)\'$/
     */
    public function theReferenceEntityWithTheLabelEqualTo(string $identifier, string $localCode, string $label)
    {
        $label = json_decode($label);

        $this->referenceEntityRepository->create(
            ReferenceEntity::create(
                ReferenceEntityIdentifier::fromString($identifier),
                [$localCode => $label],
                Image::createEmpty()
            )
        );
    }

    /**
     * @Given /^an image on a reference entity \'([^\']*)\' with path \'([^\']*)\' and filename \'([^\']*)\'$/
     */
    public function anImageOnAnReferenceEntityWitPathAndFilename(string $identifier, string $filePath, string $filename): void
    {
        $filePath = json_decode($filePath);
        $filename = json_decode($filename);

        $file = new FileInfo();
        $file->setKey($filePath);
        $file->setOriginalFilename($filename);

        $this->referenceEntityRepository->create(
            ReferenceEntity::create(
                ReferenceEntityIdentifier::fromString($identifier),
                [],
                Image::fromFileInfo($file)
            )
        );
    }

    /**
     * @When /^the user updates the image of the reference entity \'([^\']*)\' with path \'([^\']*)\' and filename \'([^\']*)\'$/
     */
    public function theUserUpdatesTheImageOfTheReferenceEntityWithPathAndFilename(string $identifier, string $filePath, string $filename): void
    {
        $filePath = json_decode($filePath);
        $filename = json_decode($filename);

        $editReferenceEntityCommand = new EditReferenceEntityCommand();
        $editReferenceEntityCommand->identifier = $identifier;
        $editReferenceEntityCommand->labels = [];
        $editReferenceEntityCommand->image = [
            'filePath' => $filePath,
            'originalFilename' => $filename
        ];
        $this->editReferenceEntity($editReferenceEntityCommand);
    }

    /**
     * @When /^the user updates the reference entity \'([^\']*)\' with the label \'([^\']*)\' equal to \'([^\']*)\'$/
     */
    public function theUserUpdatesTheReferenceEntityWithTheLabelEqualTo(string $identifier, string $localCode, string $label)
    {
        $label = json_decode($label);

        $editReferenceEntityCommand = new EditReferenceEntityCommand();
        $editReferenceEntityCommand->identifier = $identifier;
        $editReferenceEntityCommand->labels[$localCode] = $label;
        $editReferenceEntityCommand->image = null;
        $this->editReferenceEntity($editReferenceEntityCommand);
    }

    /**
     * @When /^the user updates the reference entity \'([^\']*)\' with an empty image$/
     */
    public function theUserUpdatesTheReferenceEntityWithAnEmptyImage(string $identifier)
    {
        $editReferenceEntityCommand = new EditReferenceEntityCommand();
        $editReferenceEntityCommand->identifier = $identifier;
        $editReferenceEntityCommand->labels = [];
        $editReferenceEntityCommand->image = null;
        $this->editReferenceEntity($editReferenceEntityCommand);
    }

    /**
     * @Then /^the image of the reference entity \'([^\']*)\' should be \'([^\']*)\'$/
     */
    public function theImageOfTheReferenceEntityShouldBe(string $identifier, string $filePath)
    {
        $this->constraintViolationsContext->assertThereIsNoViolations();

        $filePath = json_decode($filePath);

        $referenceEntity = $this->referenceEntityRepository
            ->getByIdentifier(ReferenceEntityIdentifier::fromString($identifier));

        Assert::assertEquals($referenceEntity->getImage()->getKey(), $filePath);
    }

    /**
     * @Then /^the reference entity \'([^\']*)\' should have an empty image$/
     */
    public function theReferenceEntityShouldHaveAnEmptyImage(string $identifier)
    {
        $this->constraintViolationsContext->assertThereIsNoViolations();

        $referenceEntity = $this->referenceEntityRepository->getByIdentifier(ReferenceEntityIdentifier::fromString($identifier));

        $referenceEntityImage = $referenceEntity->getImage();
        Assert::assertTrue($referenceEntityImage->isEmpty());
    }

    private function editReferenceEntity(EditReferenceEntityCommand $editReferenceEntityCommand): void
    {
        $this->constraintViolationsContext->addViolations($this->validator->validate($editReferenceEntityCommand));

        if (!$this->constraintViolationsContext->hasViolations()) {
            ($this->editReferenceEntityHandler)($editReferenceEntityCommand);
        }
    }
}
