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

use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\Structure\Quality;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Symfony\Component\HttpFoundation\Request;

class QualityFilterIntegration extends TestCase
{
    public function test_it_filters_attributes_by_quality()
    {
        $this->givenAnAuthenticatedUser('julia');

        $attributeGood1 = $this->givenAnAttributeWithoutSpellingMistake('attribute_good_1');
        $attributeGood2 = $this->givenAnAttributeWithoutSpellingMistake('attribute_good_2');
        $this->givenAnAttributeWithSpellingMistakes('attribute_to_improve');
        $this->givenAnAttributeWithSpellcheckInProgress('attribute_in_progress');
        $this->givenAnAttributeOnWhichSpellcheckIsNotApplicable('attribute_n_a');

        $queryParameters = [
            'attribute-grid' => [
                '_filter' => [
                    'quality' => [
                        'value' => Quality::GOOD
                    ]
                ]
            ]
        ];
        $this->get('request_stack')->push(Request::create('/datagrid/attribute-grid', 'GET', $queryParameters));
        $attributeGrid = $this->get('oro_datagrid.datagrid.manager')->getDatagrid('attribute-grid');
        $attributes = $attributeGrid->getData()->toArray();

        $this->assertArrayHasKey('data', $attributes);
        $attributesData = $attributes['data'];

        $this->assertCount(2, $attributesData);
        $this->assertAttributeExists($attributeGood1, $attributesData);
        $this->assertAttributeExists($attributeGood2, $attributesData);
    }

    private function assertAttributeExists(AttributeInterface $attribute, $attributesData)
    {
        $attributeFound = false;
        foreach ($attributesData as $attributeData) {
            if (intval($attributeData['id']) === $attribute->getId()) {
                $attributeFound = true;
                break;
            }
        }

        $this->assertTrue($attributeFound, sprintf('Attribute "%s" not found', $attribute->getCode()));
    }
}
