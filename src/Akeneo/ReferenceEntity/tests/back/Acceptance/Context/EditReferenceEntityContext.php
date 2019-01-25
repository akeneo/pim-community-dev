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

use Akeneo\ReferenceEntity\Application\ReferenceEntity\CreateReferenceEntity\CreateReferenceEntityCommand;
use Akeneo\ReferenceEntity\Application\ReferenceEntity\CreateReferenceEntity\CreateReferenceEntityHandler;
use Akeneo\ReferenceEntity\Application\ReferenceEntity\EditReferenceEntity\EditReferenceEntityCommand;
use Akeneo\ReferenceEntity\Application\ReferenceEntity\EditReferenceEntity\EditReferenceEntityHandler;
use Akeneo\ReferenceEntity\Common\Fake\InMemoryFindActivatedLocalesByIdentifiers;
use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeIdentifier;
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

    /** @var CreateReferenceEntityHandler */
    private $createReferenceEntityHandler;

    /** @var ValidatorInterface */
    private $validator;

    /** @var ConstraintViolationListInterface */
    private $violations;

    /** @var ConstraintViolationsContext */
    private $constraintViolationsContext;

    /** @var InMemoryFindActivatedLocalesByIdentifiers */
    private $activatedLocales;

    public function __construct(
        ReferenceEntityRepositoryInterface $referenceEntityRepository,
        EditReferenceEntityHandler $editReferenceEntityHandler,
        CreateReferenceEntityHandler $createReferenceEntityHandler,
        ValidatorInterface $validator,
        ConstraintViolationsContext $constraintViolationsContext,
        InMemoryFindActivatedLocalesByIdentifiers $activatedLocales
    ) {
        $this->referenceEntityRepository = $referenceEntityRepository;
        $this->editReferenceEntityHandler = $editReferenceEntityHandler;
        $this->createReferenceEntityHandler = $createReferenceEntityHandler;
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

        $createCommand = new CreateReferenceEntityCommand(
            'designer',
            [
                'en_US' => 'Designer',
                'fr_FR' => 'Concepteur'
            ]
        );

        $violations = $this->validator->validate($createCommand);
        if ($violations->count() > 0) {
            throw new \LogicException(sprintf('Cannot create reference entity: %s', $violations->get(0)->getMessage()));
        }

        ($this->createReferenceEntityHandler)($createCommand);
    }

    /**
     * @When /^the user updates the reference entity "([^"]*)" with:$/
     */
    public function theUserUpdatesTheReferenceEntityWith(string $identifier, TableNode $updateTable)
    {
        $updates = $updateTable->getRowsHash();
        $command = new EditReferenceEntityCommand(
            $identifier,
            json_decode($updates['labels'], true),
            null
        );
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

        if (key_exists('attribute_as_label', $expectedInformation)) {
            $expectedAttributeIdentifier = sprintf('%s_%s_%s',
                $expectedInformation['attribute_as_label'],
                $actualReferenceEntity->getIdentifier(),
                md5(sprintf('%s_%s', $actualReferenceEntity->getIdentifier(), $expectedInformation['attribute_as_label']))
            );

            Assert::assertTrue(
                $actualReferenceEntity->getAttributeAsLabelReference()->getIdentifier()->equals(
                    AttributeIdentifier::fromString($expectedAttributeIdentifier)
                )
            );
        }

        if (key_exists('attribute_as_image', $expectedInformation)) {
            $expectedAttributeIdentifier = sprintf('%s_%s_%s',
                $expectedInformation['attribute_as_image'],
                $actualReferenceEntity->getIdentifier(),
                md5(sprintf('%s_%s', $actualReferenceEntity->getIdentifier(), $expectedInformation['attribute_as_image']))
            );

            Assert::assertTrue(
                $actualReferenceEntity->getAttributeAsImageReference()->getIdentifier()->equals(
                    AttributeIdentifier::fromString($expectedAttributeIdentifier)
                )
            );
        }
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
        $this->activatedLocales->save(LocaleIdentifier::fromCode('en_US'));

        $label = json_decode($label);

        $createCommand = new CreateReferenceEntityCommand($identifier, [$localCode => $label]);

        $violations = $this->validator->validate($createCommand);
        if ($violations->count() > 0) {
            throw new \LogicException(sprintf('Cannot create reference entity: %s', $violations->get(0)->getMessage()));
        }

        ($this->createReferenceEntityHandler)($createCommand);
    }

    /**
     * @Given /^an image on a reference entity \'([^\']*)\' with path \'([^\']*)\' and filename \'([^\']*)\'$/
     */
    public function anImageOnAnReferenceEntityWitPathAndFilename(string $identifier, string $filePath, string $filename): void
    {
        $createCommand = new CreateReferenceEntityCommand($identifier, []);

        $violations = $this->validator->validate($createCommand);
        if ($violations->count() > 0) {
            throw new \LogicException(sprintf('Cannot create reference entity: %s', $violations->get(0)->getMessage()));
        }

        ($this->createReferenceEntityHandler)($createCommand);

        $filePath = json_decode($filePath);
        $filename = json_decode($filename);

        $file = new FileInfo();
        $file->setKey($filePath);
        $file->setOriginalFilename($filename);

        $referenceEntity = $this->referenceEntityRepository->getByIdentifier(
            ReferenceEntityIdentifier::fromString($identifier)
        );

        $referenceEntity->updateImage(Image::fromFileInfo($file));
        $this->referenceEntityRepository->update($referenceEntity);
    }

    /**
     * @When /^the user updates the image of the reference entity \'([^\']*)\' with path \'([^\']*)\' and filename \'([^\']*)\'$/
     */
    public function theUserUpdatesTheImageOfTheReferenceEntityWithPathAndFilename(string $identifier, string $filePath, string $filename): void
    {
        $filePath = json_decode($filePath);
        $filename = json_decode($filename);

        $editReferenceEntityCommand = new EditReferenceEntityCommand(
            $identifier,
            [],
            [
                'filePath' => $filePath,
                'originalFilename' => $filename
            ]
        );
        $this->editReferenceEntity($editReferenceEntityCommand);
    }

    /**
     * @When /^the user updates the reference entity \'([^\']*)\' with the label \'([^\']*)\' equal to \'([^\']*)\'$/
     */
    public function theUserUpdatesTheReferenceEntityWithTheLabelEqualTo(string $identifier, string $localCode, string $label)
    {
        $label = json_decode($label);

        $editReferenceEntityCommand = new EditReferenceEntityCommand(
            $identifier,
            [$localCode => $label],
            null
        );
        $this->editReferenceEntity($editReferenceEntityCommand);
    }

    /**
     * @When /^the user updates the reference entity \'([^\']*)\' with an empty image$/
     */
    public function theUserUpdatesTheReferenceEntityWithAnEmptyImage(string $identifier)
    {
        $editReferenceEntityCommand = new EditReferenceEntityCommand($identifier, [], null);
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
