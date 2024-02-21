<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Product\Test\Integration\Handler\Category;

use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\ChangeParent;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetIdentifierValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetSimpleSelectValue;
use Akeneo\Pim\Enrichment\Product\API\Query\GetProductCategoryCodesQuery;
use Akeneo\Test\Pim\Enrichment\Product\Integration\EnrichmentProductTestCase;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Messenger\Stamp\HandledStamp;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductCategoryCodesHandlerIntegration extends EnrichmentProductTestCase
{
    private const UUID_FOO = 'eac5393e-8de8-4d3a-90db-93bbba8b4ffb';
    private const UUID_BAR = '49aa038f-b7d9-465c-b12d-88f048d60dd4';
    private const UUID_BAZ = 'a76cbde9-929b-4d2c-8ff1-a69e48cf063d';

    /** @test */
    public function it_gets_category_codes_from_product_uuids()
    {
        $uuids = [
            Uuid::fromString(self::UUID_FOO),
            Uuid::fromString(self::UUID_BAR),
            Uuid::fromString(self::UUID_BAZ),
            Uuid::uuid4(),
        ];

        $envelope = $this->queryMessageBus->dispatch(new GetProductCategoryCodesQuery($uuids));
        $handledStamp = $envelope->last(HandledStamp::class);

        Assert::assertInstanceOf(HandledStamp::class, $handledStamp);
        Assert::assertEqualsCanonicalizing(
            [
                self::UUID_FOO => ['print', 'sales'],
                self::UUID_BAR => ['sales', 'suppliers'],
                self::UUID_BAZ => [],
            ],
            $handledStamp->getResult()
        );
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadEnrichmentProductFunctionalFixtures();
        $this->createProductModel('root_pm', 'color_variant_accessories', ['categories' => ['print']]);
        $this->createProductWithUuid(self::UUID_FOO, [
            new ChangeParent('root_pm'),
            new SetIdentifierValue('sku', 'foo'),
            new SetCategories(['sales']),
            new SetSimpleSelectValue('main_color', null, null, 'red')
        ]);
        $this->createProductWithUuid(self::UUID_BAR, [
            new SetIdentifierValue('sku', 'bar'),
            new SetCategories(['sales', 'suppliers']),
        ]);
        $this->createProductWithUuid(self::UUID_BAZ, [
            new SetIdentifierValue('sku', 'baz'),
        ]);
    }
}
