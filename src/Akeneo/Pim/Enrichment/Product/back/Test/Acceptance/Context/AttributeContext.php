<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Test\Acceptance\Context;

use Akeneo\Pim\Enrichment\Product\Test\Acceptance\InMemory\InMemoryGetAttributeTypes;
use Akeneo\Pim\Structure\Component\Model\Attribute;
use Akeneo\Pim\Structure\Component\Model\AttributeGroup;
use Akeneo\Test\Acceptance\Attribute\InMemoryAttributeRepository;
use Akeneo\Test\Acceptance\AttributeGroup\InMemoryAttributeGroupRepository;
use Akeneo\Tool\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\TableNode;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AttributeContext implements Context
{
    public function __construct(
        private InMemoryAttributeRepository $attributeRepository,
        private ObjectUpdaterInterface $attributeUpdater,
        private InMemoryAttributeGroupRepository $attributeGroupRepository,
        private InMemoryGetAttributeTypes $getAttributeTypes
    ) {
    }

    /**
     * @Given /the following attributes?:/
     */
    public function theFollowingAttribute(TableNode $table): void
    {
        foreach ($table->getHash() as $attributeData) {
            Assert::keyExists($attributeData, 'code');
            Assert::keyExists($attributeData, 'type');

            // "group" is mandatory to be able to create a valid attribute entity.
            // But in guerkins, this information can be completely useless and add noise on it
            // that's why we create on the fly a group if this data is missing.
            if (!isset($attributeData['group'])) {
                $attributeGroup = new AttributeGroup();
                $attributeGroup->setCode('other');
                $this->attributeGroupRepository->save($attributeGroup);

                $attributeData['group'] = 'other';
            }

            if (isset($attributeData['available_locales'])) {
                $attributeData['available_locales'] = array_filter(explode(',', $attributeData['available_locales']));
            }

            if (isset($attributeData['table_configuration'])) {
                if ('' === $attributeData['table_configuration']) {
                    unset($attributeData['table_configuration']);
                } else {
                    $attributeData['table_configuration'] = \json_decode($attributeData['table_configuration'], true);
                }
            }

            $attribute = new Attribute();
            $this->attributeUpdater->update($attribute, $attributeData);
            $this->attributeRepository->save($attribute);
            $this->getAttributeTypes->saveAttribute($attributeData['code'], $attributeData['type']);
        }
    }
}
