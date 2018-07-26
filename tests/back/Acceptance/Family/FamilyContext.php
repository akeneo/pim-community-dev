<?php

declare(strict_types=1);

namespace Akeneo\Test\Acceptance\Family;

use Akeneo\Pim\Structure\Component\Factory\FamilyFactory;
use Akeneo\Test\Acceptance\Attribute\InMemoryAttributeRepository;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;

final class FamilyContext implements Context
{
    /** @var InMemoryAttributeRepository */
    private $attributeRepository;

    /** @var InMemoryFamilyRepository */
    private $familyRepository;

    /** @var FamilyFactory */
    private $familyFactory;

    /**
     * @param InMemoryFamilyRepository $familyRepository
     * @param FamilyFactory $familyFactory
     * @param InMemoryAttributeRepository $attributeRepository
     */
    public function __construct(
        InMemoryFamilyRepository $familyRepository,
        FamilyFactory $familyFactory,
        InMemoryAttributeRepository $attributeRepository
    ) {
        $this->familyRepository = $familyRepository;
        $this->familyFactory = $familyFactory;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @Given the following family:
     */
    public function theFollowingFamily(TableNode $table)
    {
        foreach ($table->getHash() as $familyData) {
            $family = $this->familyFactory->create();

            $attributeCodes = explode(',', $familyData['attributes']);
            foreach ($attributeCodes as $attributeCode) {
                $attribute = $this->attributeRepository->findOneByIdentifier($attributeCode);
                if (null === $attribute) {
                    throw new \Exception(sprintf('Attribute "%s" does not exist', $attributeCode));
                }
                $family->addAttribute($attribute);
            }

            $this->familyRepository->save($family);
        }
    }
}
