<?php

declare(strict_types=1);

namespace AkeneoTestEnterprise\Pim\Permission\Integration\EventAPI;

use Akeneo\Pim\Enrichment\Component\Product\Message\ProductUpdated;
use Akeneo\Pim\Enrichment\Component\Product\Webhook\Exception\ProductNotFoundException;
use Akeneo\Pim\Enrichment\Component\Product\Webhook\ProductCreatedAndUpdatedEventDataBuilder;
use Akeneo\Platform\Component\EventQueue\Author;
use Akeneo\Platform\Component\EventQueue\BulkEvent;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use AkeneoTestEnterprise\Pim\Permission\EndToEnd\API\PermissionFixturesLoader;
use PHPUnit\Framework\Assert;

/**
 * @author    Thomas Galvaing <thomas.galvaing@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductCreatedAndUpdatedEventDataBuilderIntegration extends TestCase
{
    use AuthenticateAsTrait;

    private ProductCreatedAndUpdatedEventDataBuilder $productCreatedAndUpdatedEventDataBuilder;
    private PermissionFixturesLoader $permissionFixturesloader;

    public function setUp(): void
    {
        parent::setUp();

        $this->productCreatedAndUpdatedEventDataBuilder = $this->get(
            'pim_catalog.webhook.event_data_builder.product_created_and_updated',
        );
        $this->userRepository = $this->get('pim_user.repository.user');
        $this->permissionFixturesloader = $this->get('akeneo_integration_tests.loader.permissions');
    }

    public function test_it_apply_permissions_on_categories(): void
    {
        $this->permissionFixturesloader->loadProductModelsFixturesForCategoryPermissions();

        $productNotViewableUpdatedEvent = new ProductUpdated(Author::fromNameAndType('julia', 'ui'), [
            'identifier' => 'colored_sized_sweat_no_view',
        ]);
        $productViewableUpdatedEvent = new ProductUpdated(Author::fromNameAndType('julia', 'ui'), [
            'identifier' => 'colored_sized_shoes_view',
        ]);

        /* Necessary as `\Akeneo\Pim\Permission\Bundle\Persistence\ORM\EntityWithValue\ProductQueryBuilderFactory`
         is still using the authenticated user to fetch the categories permissions. */
        $user = $this->authenticateAs('mary');

        $collection = $this->productCreatedAndUpdatedEventDataBuilder->build(
            new BulkEvent([$productNotViewableUpdatedEvent, $productViewableUpdatedEvent]),
            $user,
        );

        $error = $collection->getEventData($productNotViewableUpdatedEvent);
        Assert::assertEquals(new ProductNotFoundException('colored_sized_sweat_no_view'), $error);

        $data = $collection->getEventData($productViewableUpdatedEvent);
        Assert::assertEquals('colored_sized_shoes_view', $data['resource']['identifier']);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
