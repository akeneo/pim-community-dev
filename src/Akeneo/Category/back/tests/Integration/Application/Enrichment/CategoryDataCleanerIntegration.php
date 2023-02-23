<?php

namespace Akeneo\Category\back\tests\Integration\Application\Enrichment;

use Akeneo\Category\Application\Enrichment\CategoryDataCleaner;
use Akeneo\Category\Application\Enrichment\Filter\ChannelAndLocalesFilter;
use Akeneo\Category\Application\Query\GetEnrichedCategoryValuesOrderedByCategoryCode;
use Akeneo\Category\Application\Storage\UpdateCategoryEnrichedValues;
use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;
use Akeneo\Category\Domain\ValueObject\Attribute\Value\AbstractValue;

class CategoryDataCleanerIntegration extends CategoryTestCase
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
        $this->assertTrue(array_key_exists($this->getMobileTitleKey('en_US'), json_decode($currentValuesByCategoryCode['socks'], true)));
        $this->assertTrue(array_key_exists($this->getMobileTitleKey('en_US'), json_decode($currentValuesByCategoryCode['pants'], true)));

        // clean enriched categories data linked to 'mobile' channel
        $this->get(CategoryDataCleaner::class)->__invoke(
            [
                'channel_code' => 'mobile',
                'action' => ChannelAndLocalesFilter::CLEAN_CHANNEL_ACTION,
            ],
            new ChannelAndLocalesFilter()
        );
        $cleanedValuesByCategoryCode = $this->get(GetEnrichedCategoryValuesOrderedByCategoryCode::class)->byLimitAndOffset(100, 0);

        $cleanedSocksValueCollection = json_decode($cleanedValuesByCategoryCode['socks'], true);
        $cleanedPantsValueCollection = json_decode($cleanedValuesByCategoryCode['pants'], true);

        $this->assertTrue(array_key_exists($this->getEcommerceTitleKey('en_US'), $cleanedSocksValueCollection));
        $this->assertEquals(
            'ecommerce',
            $cleanedSocksValueCollection[$this->getEcommerceTitleKey('en_US')]['channel']
        );
        $this->assertFalse(array_key_exists($this->getMobileTitleKey('en_US'), $cleanedSocksValueCollection));
        $this->assertFalse(array_key_exists($this->getMobileTitleKey('fr_FR'), $cleanedSocksValueCollection));

        $this->assertTrue(array_key_exists($this->getEcommerceTitleKey('en_US'), $cleanedPantsValueCollection));
        $this->assertEquals(
            'ecommerce',
            $cleanedPantsValueCollection[$this->getEcommerceTitleKey('en_US')]['channel']
        );
        $this->assertFalse(array_key_exists($this->getMobileTitleKey('en_US'), $cleanedPantsValueCollection));
        $this->assertFalse(array_key_exists($this->getMobileTitleKey('fr_FR'), $cleanedPantsValueCollection));

        $this->assertTrue(array_key_exists($this->getNotScopableNotLocalizablePhotoKey(), $cleanedSocksValueCollection));
        $this->assertTrue(array_key_exists($this->getNotScopableNotLocalizablePhotoKey(), $cleanedPantsValueCollection));
    }

    public function testItCleanEnrichedValuesLinkedToDeactivateChannelLocale(): void
    {
        $currentValuesByCategoryCode = $this->get(GetEnrichedCategoryValuesOrderedByCategoryCode::class)->byLimitAndOffset(100, 0);
        $this->assertTrue(array_key_exists($this->getMobileTitleKey('en_US'), json_decode($currentValuesByCategoryCode['socks'], true)));
        $this->assertTrue(array_key_exists($this->getMobileTitleKey('en_US'), json_decode($currentValuesByCategoryCode['pants'], true)));

        // clean enriched categories data linked to 'mobile' channel
        $this->get(CategoryDataCleaner::class)->__invoke(
            [
                'channel_code' => 'mobile',
                'locales_codes' => ['fr_FR'],
                'action' => ChannelAndLocalesFilter::CLEAN_CHANNEL_LOCALE_ACTION,
            ],
            new ChannelAndLocalesFilter()
        );
        $cleanedValuesByCategoryCode = $this->get(GetEnrichedCategoryValuesOrderedByCategoryCode::class)->byLimitAndOffset(100, 0);

        $cleanedSocksValueCollection = json_decode($cleanedValuesByCategoryCode['socks'], true);
        $cleanedPantsValueCollection = json_decode($cleanedValuesByCategoryCode['pants'], true);

        $this->assertTrue(array_key_exists($this->getEcommerceTitleKey('en_US'), $cleanedSocksValueCollection));
        $this->assertEquals(
            'ecommerce',
            $cleanedSocksValueCollection[$this->getEcommerceTitleKey('en_US')]['channel']
        );
        $this->assertFalse(array_key_exists($this->getMobileTitleKey('en_US'), $cleanedSocksValueCollection));
        $this->assertTrue(array_key_exists($this->getMobileTitleKey('fr_FR'), $cleanedSocksValueCollection));

        $this->assertTrue(array_key_exists($this->getEcommerceTitleKey('en_US'), $cleanedPantsValueCollection));
        $this->assertEquals(
            'ecommerce',
            $cleanedPantsValueCollection[$this->getEcommerceTitleKey('en_US')]['channel']
        );
        $this->assertFalse(array_key_exists($this->getMobileTitleKey('en_US'), $cleanedPantsValueCollection));
        $this->assertTrue(array_key_exists($this->getMobileTitleKey('fr_FR'), $cleanedPantsValueCollection));
    }

    private function addMobileChannelToAlreadyEnrichedCategories(): void
    {
        $valuesByCategoryCode = $this->get(GetEnrichedCategoryValuesOrderedByCategoryCode::class)->byLimitAndOffset(100, 0);

        $socksValueCollection = json_decode($valuesByCategoryCode['socks'], true);
        $pantsValueCollection = json_decode($valuesByCategoryCode['pants'], true);

        // add 'mobile' channel value to 'title' attribute
        $socksValueCollection[$this->getMobileTitleKey('en_US')] = [
            'data' => 'Socks you need on phone',
            'type' => 'text',
            'channel' => 'mobile',
            'locale' => 'en_US',
            'attribute_code' => 'title'.AbstractValue::SEPARATOR.'87939c45-1d85-4134-9579-d594fff65030',
        ];

        $socksValueCollection[$this->getMobileTitleKey('fr_FR')] = [
            'data' => 'Socks you need on phone',
            'type' => 'text',
            'channel' => 'mobile',
            'locale' => 'fr_FR',
            'attribute_code' => 'title'.AbstractValue::SEPARATOR.'87939c45-1d85-4134-9579-d594fff65030',
        ];

        // add 'mobile' channel value to 'title' attribute
        $pantsValueCollection[$this->getMobileTitleKey('en_US')] = [
            'data' => 'Pants you need on phone',
            'type' => 'text',
            'channel' => 'mobile',
            'locale' => 'en_US',
            'attribute_code' => 'title'.AbstractValue::SEPARATOR.'87939c45-1d85-4134-9579-d594fff65030',
        ];

        $pantsValueCollection[$this->getMobileTitleKey('fr_FR')] = [
            'data' => 'Pants you need on phone',
            'type' => 'text',
            'channel' => 'mobile',
            'locale' => 'fr_FR',
            'attribute_code' => 'title'.AbstractValue::SEPARATOR.'87939c45-1d85-4134-9579-d594fff65030',
        ];


        $updatedBatch =  [
            'socks' => json_encode($socksValueCollection),
            'pants' => json_encode($pantsValueCollection),
        ];

        $this->get(UpdateCategoryEnrichedValues::class)->execute($updatedBatch);
    }

    private function getMobileTitleKey(string $localCode): string
    {
        return 'title'
            .AbstractValue::SEPARATOR.'87939c45-1d85-4134-9579-d594fff65030'
            .AbstractValue::SEPARATOR.'mobile'
            .AbstractValue::SEPARATOR.$localCode;
    }

    private function getEcommerceTitleKey(string $localCode): string
    {
        return 'title'
            .AbstractValue::SEPARATOR.'87939c45-1d85-4134-9579-d594fff65030'
            .AbstractValue::SEPARATOR.'ecommerce'
            .AbstractValue::SEPARATOR.$localCode;
    }

    private function getNotScopableNotLocalizablePhotoKey(): string
    {
        return 'photo'.AbstractValue::SEPARATOR.'8587cda6-58c8-47fa-9278-033e1d8c735c';
    }
}
