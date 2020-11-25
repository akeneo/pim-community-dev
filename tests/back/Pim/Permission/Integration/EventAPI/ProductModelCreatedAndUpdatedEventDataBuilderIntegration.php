<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Permission\Integration\EventAPI;

use Akeneo\Pim\Enrichment\Component\Product\Message\ProductModelUpdated;
use Akeneo\Pim\Enrichment\Component\Product\Webhook\Exception\ProductModelNotFoundException;
use Akeneo\Pim\Enrichment\Component\Product\Webhook\ProductModelCreatedAndUpdatedEventDataBuilder;
use Akeneo\Platform\Component\EventQueue\Author;
use Akeneo\Platform\Component\EventQueue\BulkEvent;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use AkeneoTestEnterprise\Pim\Permission\EndToEnd\API\PermissionFixturesLoader;
use PHPUnit\Framework\Assert;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelCreatedAndUpdatedEventDataBuilderIntegration extends TestCase
{
    use AuthenticateAsTrait;

    private ProductModelCreatedAndUpdatedEventDataBuilder $productModelCreatedAndUpdatedEventDataBuilder;
    private PermissionFixturesLoader $permissionFixturesloader;

    public function setUp(): void
    {
        parent::setUp();

        $this->productModelCreatedAndUpdatedEventDataBuilder = $this->get(
            'pim_catalog.webhook.event_data_builder.product_model_created_and_updated',
        );
        $this->permissionFixturesloader = $this->get('akeneo_integration_tests.loader.permissions');
    }

    public function test_it_apply_permissions_on_categories(): void
    {
        $this->permissionFixturesloader->loadProductModelsFixturesForCategoryPermissions();

        $productModelNotViewableUpdatedEvent = new ProductModelUpdated(Author::fromNameAndType('julia', 'ui'), [
            'code' => 'sweat_no_view',
        ]);
        $productModelViewableUpdatedEvent = new ProductModelUpdated(Author::fromNameAndType('julia', 'ui'), [
            'code' => 'shoes_view',
        ]);

        /* Necessary as `\Akeneo\Pim\Permission\Bundle\Persistence\ORM\EntityWithValue\ProductQueryBuilderFactory`
         is still using the authenticated user to fetch the categories permissions. */
        $user = $this->authenticateAs('mary');

        $collection = $this->productModelCreatedAndUpdatedEventDataBuilder->build(
            new BulkEvent([$productModelNotViewableUpdatedEvent, $productModelViewableUpdatedEvent]),
            $user,
        );

        $error = $collection->getEventData($productModelNotViewableUpdatedEvent);
        Assert::assertEquals(new ProductModelNotFoundException('sweat_no_view'), $error);

        $data = $collection->getEventData($productModelViewableUpdatedEvent);
        Assert::assertEquals('shoes_view', $data['resource']['code']);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
