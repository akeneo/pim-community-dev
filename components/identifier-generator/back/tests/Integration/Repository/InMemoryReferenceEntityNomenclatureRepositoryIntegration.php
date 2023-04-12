<?php

declare(strict_types=1);

namespace Integration\Repository;

use Akeneo\Pim\Automation\IdentifierGenerator\Domain\Model\NomenclatureDefinition;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\Pim\Enrichment\Product\Helper\FeatureHelper;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InMemoryReferenceEntityNomenclatureRepositoryIntegration extends ControllerEndToEndTest
{

    protected function setUp(): void
    {
    }

    /** @test */
    public function it_saves_ref_entity_nomenclature(): void
    {
        FeatureHelper::skipIntegrationTestWhenReferenceEntityIsNotActivated();

        $refEntityNomenclatureDefinition = new NomenclatureDefinition(
            '=',
            5,
            false,
            []
        );

    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog(['identifier_generator']);
    }
}
