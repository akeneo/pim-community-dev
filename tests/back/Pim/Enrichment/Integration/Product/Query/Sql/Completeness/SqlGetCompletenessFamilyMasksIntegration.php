<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Product\Query\Sql\Completeness;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\Query\GetCompletenessFamilyMasks;
use Akeneo\Test\Integration\TestCase;

class SqlGetCompletenessFamilyMasksIntegration extends TestCase
{
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    public function test_that_every_mask_contains_sku()
    {
        $result = $this->getCompletenessFamilyMasks()->fromFamilyCodes(['familyA']);
        foreach ($result['familyA']->masks() as $mask) {
            $this->assertContains('sku-<all_channels>-<all_locales>', $mask->mask());
        }
    }

    public function test_that_non_scopable_neither_localizable_contains_all_channels_and_all_locales()
    {
        $result = $this->getCompletenessFamilyMasks()->fromFamilyCodes(['familyA']);
        $this->assertTabletFrContainsMask($result, 'a_date-<all_channels>-<all_locales>');
    }

    public function test_that_localizable_contains_all_channels()
    {
        $result = $this->getCompletenessFamilyMasks()->fromFamilyCodes(['familyA']);
        $this->assertTabletFrContainsMask($result, 'a_localizable_image-<all_channels>-fr_FR');
    }

    public function test_that_localizable_and_scopable_contains_specific_locale_and_channel()
    {
        $result = $this->getCompletenessFamilyMasks()->fromFamilyCodes(['familyA']);
        $this->assertTabletFrContainsMask($result, 'a_localized_and_scopable_text_area-tablet-fr_FR');
    }

    public function test_that_price_attributes_contains_currencies()
    {
        $result = $this->getCompletenessFamilyMasks()->fromFamilyCodes(['familyA']);
        $this->assertTabletFrContainsMask($result, 'a_scopable_price-EUR-USD-tablet-<all_locales>');
    }

    private function getCompletenessFamilyMasks(): GetCompletenessFamilyMasks
    {
        return $this->get('akeneo.pim.enrichment.completeness.query.get_family_masks');
    }

    private function assertTabletFrContainsMask(array $result, string $string)
    {
        foreach ($result['familyA']->masks() as $mask) {
            if ($mask->channelCode() === 'tablet' && $mask->localeCode() === 'fr_FR') {
                $this->assertContains($string, $mask->mask());

                return;
            }
        }

        throw new \LogicException('Mask with channel "tablet" and locale "fr_FR" not found');
    }
}
