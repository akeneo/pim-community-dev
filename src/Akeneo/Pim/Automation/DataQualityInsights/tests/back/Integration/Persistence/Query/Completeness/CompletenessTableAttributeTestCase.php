<?php

declare(strict_types=1);

namespace Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Persistence\Query\Completeness;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\AttributeGroupActivation;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\AttributeGroupCode;
use Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject\ProductId;
use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Persistence\Repository\AttributeGroupActivationRepository;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\DataQualityInsightsTestCase;
use Akeneo\Test\Pim\Automation\DataQualityInsights\Integration\Infrastructure\Persistence\Query\Completeness\CompletenessTestCase;
use Webmozart\Assert\Assert;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class CompletenessTableAttributeTestCase extends CompletenessTestCase
{
    protected function givenAttributes(array $attributesData): void
    {
        $attributes = array_map(function ($attributeData) {
            $attribute = $this->get('pim_catalog.factory.attribute')->create();
            $this->get('pim_catalog.updater.attribute')->update(
                $attribute,
                [
                    'code' => $attributeData['code'],
                    'type' => $attributeData['type'],
                    'localizable' => $attributeData['localizable'] ?? false,
                    'scopable' => $attributeData['scopable'] ?? false,
                    'group' => $attributeData['group'] ?? 'other',
                    'available_locales' => $attributeData['available_locales'] ?? [],
                    'decimals_allowed' => $attributeData['type'] === AttributeTypes::PRICE_COLLECTION ? false : null,
                ]
            );

            if ($attributeData['type'] === AttributeTypes::TABLE && $attributeData['table_configuration']) {
                $this->get('pim_catalog.updater.attribute')->update(
                    $attribute,
                    [
                        'table_configuration' => $attributeData['table_configuration'],
                    ]
                );
            }

            $errors = $this->get('validator')->validate($attribute);
            Assert::count($errors, 0);

            return $attribute;
        }, $attributesData);

        $this->get('pim_catalog.saver.attribute')->saveAll($attributes);
    }
}
