<?php

declare(strict_types=1);

namespace Akeneo\Test\Acceptance\AttributeOption;

use Akeneo\Test\Common\EntityBuilder;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;

class AttributeOptionContext implements Context
{
    /** @var EntityBuilder */
    private $attributeOptionBuilder;

    /** @var InMemoryAttributeOptionRepository */
    private $attributeOptionRepository;

    public function __construct(
        EntityBuilder $attributeOptionBuilder,
        InMemoryAttributeOptionRepository $attributeOptionRepository
    ) {
        $this->attributeOptionBuilder = $attributeOptionBuilder;
        $this->attributeOptionRepository = $attributeOptionRepository;
    }

    /**
     * @Given the following attribute options:
     */
    public function theFollowingAttributeOptions(TableNode $table): void
    {
        foreach ($table as $row) {
            $option = $this->attributeOptionBuilder->build($row);
            $this->attributeOptionRepository->save($option);
        }
    }
}
