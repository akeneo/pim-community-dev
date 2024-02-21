<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Structure\Integration\Attribute\Query;

use Akeneo\Pim\Structure\Component\Exception\CannotRemoveAttributeException;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Webmozart\Assert\Assert;

class CheckAttributeIsNotUsedAsChannelConversionUnitOnDeletionSubscriberIntegration extends TestCase
{
    public function test_it_throws_an_exception_when_the_attribute_is_used_as_conversion_unit(): void
    {
        $this->givenAttribute([
            'code' => 'weight',
            'type' => 'pim_catalog_metric',
            'metric_family' => 'Weight',
            'default_metric_unit' => 'GRAM',
            'decimals_allowed' => true,
            'negative_allowed' => false,
            'group' => 'other',
        ]);

        $this->givenChannel([
            'code' => 'new_channel',
            'category_tree' => 'master',
            'currencies'       => ['EUR'],
            'locales'          => ['fr_FR'],
            'conversion_units' => [
                'weight' => 'GRAM',
            ],
        ]);

        $attribute = $this->get('pim_catalog.repository.attribute')->findOneByIdentifier('weight');

        $this->expectException(CannotRemoveAttributeException::class);
        $this->get('pim_catalog.remover.attribute')->remove($attribute);
    }

    private function givenAttribute(array $attributeData): void
    {
        $attribute = $this->get('pim_catalog.factory.attribute')->create();
        $this->get('pim_catalog.updater.attribute')->update($attribute, $attributeData);

        $constraintViolations = $this->get('validator')->validate($attribute);
        Assert::count($constraintViolations, 0);

        $this->get('pim_catalog.saver.attribute')->save($attribute);
    }

    private function givenChannel(array $channelData): void
    {
        $channel = $this->get('pim_catalog.factory.channel')->create();
        $this->get('pim_catalog.updater.channel')->update($channel, $channelData);

        $constraintViolations = $this->get('validator')->validate($channel);
        Assert::count($constraintViolations, 0);

        $this->get('pim_catalog.saver.channel')->save($channel);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }
}
