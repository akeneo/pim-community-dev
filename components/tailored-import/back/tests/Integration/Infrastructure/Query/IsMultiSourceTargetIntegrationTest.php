<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredImport\Test\Integration\Infrastructure\Query;

use Akeneo\Platform\TailoredImport\Infrastructure\Query\IsMultiSourceTarget;
use Akeneo\Platform\TailoredImport\Test\Integration\IntegrationTestCase;
use Akeneo\Test\Integration\Configuration;

class IsMultiSourceTargetIntegrationTest extends IntegrationTestCase
{
    public function test_it_returns_if_an_attribute_is_a_multi_source_target(): void
    {
        $singleAttributeCode = 'composition';
        $collectionAttributeCode = 'collection';
        $unknownAttributeCode = 'unknown_attribute';

        $this->assertTrue($this->getQuery()->isAttributeMultiSourceTarget($collectionAttributeCode));

        $this->assertFalse($this->getQuery()->isAttributeMultiSourceTarget($singleAttributeCode));

        $this->expectExceptionObject(
            new \InvalidArgumentException(
                sprintf(
                    'Attribute "%s" does not exist',
                    $unknownAttributeCode
                )
            )
        );
        $this->getQuery()->isAttributeMultiSourceTarget($unknownAttributeCode);
    }

    public function test_it_returns_if_a_system_property_is_a_multi_source_target(): void
    {
        $singlePropertyCode = 'enabled';
        $collectionPropertyCode = 'categories';
        $unknownPropertyCode = 'unknown_property';

        $this->assertTrue($this->getQuery()->isSystemPropertyMultiSourceTarget($collectionPropertyCode));

        $this->assertFalse($this->getQuery()->isSystemPropertyMultiSourceTarget($singlePropertyCode));
        $this->assertFalse($this->getQuery()->isSystemPropertyMultiSourceTarget($unknownPropertyCode));
    }


    private function getQuery(): IsMultiSourceTarget
    {
        return $this->get('akeneo.tailored_import.query.is_multi_source_target');
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }
}
