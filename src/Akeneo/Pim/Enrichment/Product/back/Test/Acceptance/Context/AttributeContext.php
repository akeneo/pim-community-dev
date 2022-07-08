<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Test\Acceptance\Context;

use Akeneo\Pim\Enrichment\Product\Test\Acceptance\InMemory\InMemoryGetAttributeTypes;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AttributeContext implements Context
{
    public function __construct(private InMemoryGetAttributeTypes $getAttributeTypes)
    {
    }

    /**
     * @Given /the following attributes?:/
     */
    public function theFollowingAttribute(TableNode $table): void
    {
        foreach ($table->getHash() as $attributeData) {
            Assert::keyExists($attributeData, 'code');
            Assert::keyExists($attributeData, 'type');

            $this->getAttributeTypes->saveAttribute($attributeData['code'], $attributeData['type']);
        }
    }
}
