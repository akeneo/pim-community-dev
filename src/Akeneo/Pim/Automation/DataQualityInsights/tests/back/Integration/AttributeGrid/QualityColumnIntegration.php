<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\AttributeGrid;

use Symfony\Component\HttpFoundation\Request;

class QualityColumnIntegration extends TestCase
{
    public function test_the_attribute_grid_has_a_column_quality()
    {
        $this->givenAnAuthenticatedUser('julia');

        $attributeGood = $this->givenAnAttributeWithoutSpellingMistake('attribute_good');
        $attributeToImprove = $this->givenAnAttributeWithSpellingMistakes('attribute_to_improve');
        $attributeInProgress = $this->givenAnAttributeWithSpellcheckInProgress('attribute_in_progress');
        $attributeNotApplicable = $this->givenAnAttributeOnWhichSpellcheckIsNotApplicable('attribute_n_a');

        $this->get('request_stack')->push(Request::create('/datagrid/attribute-grid'));
        $attributeGrid = $this->get('oro_datagrid.datagrid.manager')->getDatagrid('attribute-grid');
        $attributes = $attributeGrid->getData()->toArray();

        $this->assertArrayHasKey('data', $attributes);
        $this->assertAttributeQualityEquals('good', $attributeGood->getId(), $attributes['data']);
        $this->assertAttributeQualityEquals('to_improve', $attributeToImprove->getId(), $attributes['data']);
        $this->assertAttributeQualityEquals('in_progress', $attributeInProgress->getId(), $attributes['data']);
        $this->assertAttributeQualityEquals('n_a', $attributeNotApplicable->getId(), $attributes['data']);
    }

    private function assertAttributeQualityEquals(string $expectedQuality, int $attributeId, array $attributes): void
    {
        $attributeData = [];
        foreach ($attributes as $attribute) {
            if (intval($attribute['id']) === $attributeId) {
                $attributeData = $attribute;
                break;
            }
        }

        $this->assertNotNull($attributeData);
        $this->assertArrayHasKey('quality', $attributeData);
        $this->assertSame($expectedQuality, $attributeData['quality']);
    }
}
