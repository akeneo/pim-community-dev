<?php

namespace PimEnterprise\Component\CatalogRule\tests\integration\Validator;

use Akeneo\Test\Integration\TestCase;
use PimEnterprise\Component\CatalogRule\Model\ProductCondition;

class ExistingFilterFieldValidatorIntegration extends TestCase
{
    /**
     * @see https://akeneo.atlassian.net/browse/PIM-7146
     */
    public function testValidationRuleWithCompletenessCondition()
    {
        $condition = [
            'field' => 'completeness',
            'operator' => 'NOT EQUALS ON AT LEAST ONE LOCALE',
            'value' => '50',
            'locale' => 'en_US',
            'scope' => 'ecommerce',
        ];
        $productCondition = new ProductCondition($condition);

        $validator = $this->getFromTestContainer('validator');

        $violations = $validator->validate($productCondition);

        $this->assertSame(0, $violations->count());
    }

    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
