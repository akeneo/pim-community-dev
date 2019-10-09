<?php

declare(strict_types=1);

namespace Akeneo\Test\Acceptance\Family;

use Akeneo\Pim\Structure\Component\Factory\FamilyFactory;
use Akeneo\Pim\Structure\Component\Updater\FamilyUpdater;
use Akeneo\Test\Acceptance\Attribute\InMemoryAttributeRepository;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2019 Akeneo SAS (https://www.akeneo.com)
 */
class FamilyContext implements Context
{
    /** @var InMemoryFamilyRepository */
    private $familyRepository;

    /** @var FamilyFactory */
    private $familyFactory;

    /** @var FamilyUpdater */
    private $familyUpdater;

    /** @var InMemoryAttributeRepository */
    private $attributeRepository;

    public function __construct(
        InMemoryFamilyRepository $familyRepository,
        FamilyFactory $familyFactory,
        FamilyUpdater $familyUpdater,
        InMemoryAttributeRepository $attributeRepository
    ) {
        $this->familyRepository = $familyRepository;
        $this->familyFactory = $familyFactory;
        $this->familyUpdater = $familyUpdater;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @Given the following family:
     */
    public function theFollowingFamily(TableNode $table)
    {
        foreach ($table->getHash() as $familyData) {
            $family = $this->familyFactory->create();
            $this->familyUpdater->update($family, $familyData);
            $this->familyRepository->save($family);
        }
    }

    /**
     * @Given /^the family has the "([^"]*)" attribute$/
     */
    public function theFamilyHasTheAttribute(string $attributeCode)
    {
        $family = $this->familyRepository->findOneByIdentifier('my_family');
        $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);

        $family->addAttribute($attribute);

        $this->familyRepository->save($family);
    }
}
