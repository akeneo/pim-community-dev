<?php

namespace Akeneo\Category\back\tests\Integration\Application\Enrichment;

use Akeneo\Category\Application\Enrichment\CleanCategoryDataLinkedToChannel;
use Akeneo\Category\Application\Query\GetEnrichedCategoryValuesOrderedByCategoryCode;
use Akeneo\Category\Application\Storage\UpdateCategoryEnrichedValues;
use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;
use Akeneo\Category\Domain\ValueObject\Attribute\Value\AbstractValue;

class CleanCategoryDataLinkedToChannelIntegration extends CategoryTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $categorySocks = $this->createCategory([
            'code' => 'socks',
            'labels' => [
                'fr_FR' => 'Chaussettes',
                'en_US' => 'Socks'
            ]
        ]);

        $categoryPants = $this->createCategory([
            'code' => 'pants',
            'labels' => [
                'fr_FR' => 'Pantalons',
                'en_US' => 'Pants'
            ]
        ]);

        $this->updateCategoryWithValues($categorySocks->getCode());
        $this->updateCategoryWithValues($categoryPants->getCode());
        $this->addMobileChannelToAlreadyEnrichedCategories();
    }

    public function testItCleanEnrichedValuesLinkedToDeletedChannel(): void
    {
        $currentValuesByCategoryCode = $this->get(GetEnrichedCategoryValuesOrderedByCategoryCode::class)->byLimitAndOffset(100, 0);
        $this->assertTrue(array_key_exists($this->getMobileTitleKey(), json_decode($currentValuesByCategoryCode['socks'], true)));
        $this->assertTrue(array_key_exists($this->getMobileTitleKey(), json_decode($currentValuesByCategoryCode['pants'], true)));

        // clean enriched categories data linked to 'mobile' channel
        $this->get(CleanCategoryDataLinkedToChannel::class)->__invoke('mobile');
        $cleanedValuesByCategoryCode = $this->get(GetEnrichedCategoryValuesOrderedByCategoryCode::class)->byLimitAndOffset(100, 0);

        $cleanedSocksValueCollection = json_decode($cleanedValuesByCategoryCode['socks'], true);
        $cleanedPantsValueCollection = json_decode($cleanedValuesByCategoryCode['pants'], true);

        $this->assertTrue(array_key_exists($this->getEcommerceTitleKey(), $cleanedSocksValueCollection));
        $this->assertEquals(
            'ecommerce',
            $cleanedSocksValueCollection[$this->getEcommerceTitleKey()]['channel']
        );
        $this->assertFalse(array_key_exists($this->getMobileTitleKey(), $cleanedSocksValueCollection));

        $this->assertTrue(array_key_exists($this->getEcommerceTitleKey(), $cleanedPantsValueCollection));
        $this->assertEquals(
            'ecommerce',
            $cleanedPantsValueCollection[$this->getEcommerceTitleKey()]['channel']
        );
        $this->assertFalse(array_key_exists($this->getMobileTitleKey(), $cleanedPantsValueCollection));
    }

    private function addMobileChannelToAlreadyEnrichedCategories(): void
    {
        $valuesByCategoryCode = $this->get(GetEnrichedCategoryValuesOrderedByCategoryCode::class)->byLimitAndOffset(100, 0);

        $socksValueCollection = json_decode($valuesByCategoryCode['socks'], true);
        $pantsValueCollection = json_decode($valuesByCategoryCode['pants'], true);

        // add 'mobile' channel value to 'title' attribute
        $socksValueCollection[$this->getMobileTitleKey()] = [
            'data' => 'Socks you need on phone',
            'type' => 'text',
            'channel' => 'mobile',
            'locale' => 'en_US',
            'attribute_code' => 'title'.AbstractValue::SEPARATOR.'87939c45-1d85-4134-9579-d594fff65030',
        ];

        // add 'mobile' channel value to 'title' attribute
        $pantsValueCollection[$this->getMobileTitleKey()] = [
            'data' => 'Pants you need on phone',
            'type' => 'text',
            'channel' => 'mobile',
            'locale' => 'en_US',
            'attribute_code' => 'title'.AbstractValue::SEPARATOR.'87939c45-1d85-4134-9579-d594fff65030',
        ];

        $updatedBatch =  [
            'socks' => json_encode($socksValueCollection),
            'pants' => json_encode($pantsValueCollection),
        ];

        $this->get(UpdateCategoryEnrichedValues::class)->execute($updatedBatch);
    }

    private function getMobileTitleKey(): string
    {
        return 'title'
            .AbstractValue::SEPARATOR.'87939c45-1d85-4134-9579-d594fff65030'
            .AbstractValue::SEPARATOR.'mobile'
            .AbstractValue::SEPARATOR.'en_US';
    }

    private function getEcommerceTitleKey(): string
    {
        return 'title'
            .AbstractValue::SEPARATOR.'87939c45-1d85-4134-9579-d594fff65030'
            .AbstractValue::SEPARATOR.'ecommerce'
            .AbstractValue::SEPARATOR.'en_US';
    }
}
