<?php

namespace AkeneoTestEnterprise\Pim\Permission\EndToEnd\API\Product;

use Akeneo\Channel\Infrastructure\Component\Model\Channel;
use Akeneo\Channel\Infrastructure\Component\Model\Locale;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\Association\AssociateProducts;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetCategories;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetImageValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMeasurementValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetMultiSelectValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetNumberValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetTextareaValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Akeneo\Pim\Enrichment\Product\API\ValueObject\ProductIdentifier;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\EntityWithValuesDraftInterface;
use Akeneo\Tool\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Akeneo\UserManagement\Component\Model\UserInterface;
use AkeneoTest\Pim\Enrichment\Integration\Normalizer\NormalizedProductCleaner;
use PHPUnit\Framework\Assert;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

abstract class AbstractProductTestCase extends ApiTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->createProduct('product_viewable_by_everybody_1', [
            new SetCategories(['categoryA2']),
            new SetTextareaValue('a_localized_and_scopable_text_area', 'ecommerce', 'en_US','EN ecommerce'),
            new SetTextareaValue('a_localized_and_scopable_text_area', 'tablet', 'fr_FR','FR ecommerce'),
            new SetTextareaValue('a_localized_and_scopable_text_area', 'tablet', 'de_DE','DE ecommerce'),
            new SetNumberValue('a_number_float', null, null, '12.05'),
            new SetImageValue('a_localizable_image', null, 'en_US', $this->getFileInfoKey($this->getFixturePath('akeneo.jpg'))),
            new SetImageValue('a_localizable_image', null, 'fr_FR', $this->getFileInfoKey($this->getFixturePath('akeneo.jpg'))),
            new SetImageValue('a_localizable_image', null, 'de_DE', $this->getFileInfoKey($this->getFixturePath('akeneo.jpg'))),
            new SetMeasurementValue('a_metric_without_decimal_negative', null, null, -10, 'CELSIUS'),
            new SetMultiSelectValue('a_multi_select', null, null, ['optionA', 'optionB']),
        ]);

        $this->createProduct('product_viewable_by_everybody_2', [
            new SetCategories(['categoryA2', 'categoryB'])
        ]);

        $this->createProduct('product_not_viewable_by_redactor', [
            new SetCategories(['categoryB'])
        ]);

        $this->createProduct('product_without_category', [
            new AssociateProducts('X_SELL', ['product_viewable_by_everybody_2', 'product_not_viewable_by_redactor']),
        ]);

        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
        $this->get('pim_connector.doctrine.cache_clearer')->clear();
        $this->get('pim_catalog.validator.unique_value_set')->reset();
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog(featureFlags: ['permission']);
    }

    /**
     * @param UserIntent[]  $userIntents
     */
    protected function createProduct(string $identifier, array $userIntents = []): ProductInterface
    {
        $this->loginAs('admin');

        $this->getCommandMessageBus()->dispatch(
            UpsertProductCommand::createWithIdentifier($this->getUserId('admin'), ProductIdentifier::fromIdentifier($identifier), $userIntents)
        );
        $this->refreshIndex();

        return $this->get('pim_catalog.repository.product')->findOneByIdentifier($identifier);
    }

    protected function getUserId(string $username): int
    {
        $query = <<<SQL
            SELECT id FROM oro_user WHERE username = :username
        SQL;
        $stmt = $this->get('database_connection')->executeQuery($query, ['username' => $username]);
        $id = $stmt->fetchOne();
        if (null === $id) {
            throw new \InvalidArgumentException(\sprintf('No user exists with username "%s"', $username));
        }

        return \intval($id);
    }

    protected function loginAs(string $username): int
    {
        $user = $this->get('pim_user.repository.user')->findOneByIdentifier($username);
        Assert::assertInstanceOf(UserInterface::class, $user);
        $this->get('security.token_storage')->setToken(
            new UsernamePasswordToken($user, 'main', $user->getRoles())
        );

        return (int) $user->getId();
    }

    protected function refreshIndex(): void
    {
        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
    }

    private function getCommandMessageBus(): MessageBusInterface
    {
        return $this->get('pim_enrich.product.message_bus');
    }

    /**
     * @param string           $userName
     * @param ProductInterface $product
     * @param array            $changes
     *
     * @return EntityWithValuesDraftInterface
     */
    protected function createEntityWithValuesDraft(
        string $userName,
        ProductInterface $product,
        array $changes
    ) : EntityWithValuesDraftInterface {
        $this->get('pim_catalog.updater.product')->update($product, $changes);

        // @todo[DAPI-443] avoid the coupling with the bounded context Workflow
        $user = $this->get('pim_user.provider.user')->loadUserByUsername($userName);

        $productDraft = $this->get('pimee_workflow.product.builder.draft')->build(
            $product,
            $this->get('Akeneo\Pim\WorkOrganization\Workflow\Component\Factory\PimUserDraftSourceFactory')->createFromUser($user)
        );
        $this->get('pimee_workflow.saver.product_draft')->save($productDraft);

        return $productDraft;
    }

    /**
     * @param Response $response
     * @param string   $expected
     */
    protected function assertListResponse(Response $response, $expected)
    {
        $result = json_decode($response->getContent(), true);
        $expected = json_decode($expected, true);

        if (!isset($result['_embedded'])) {
            Assert::fail($response->getContent());
        }

        foreach ($result['_embedded']['items'] as $index => $product) {
            NormalizedProductCleaner::clean($result['_embedded']['items'][$index]);

            if (isset($expected['_embedded']['items'][$index])) {
                NormalizedProductCleaner::clean($expected['_embedded']['items'][$index]);
            }
        }

        $this->assertEquals($expected, $result);
    }

    /**
     * @param string $sql
     *
     * @return array
     */
    protected function getDatabaseData(string $sql): array
    {
        $stmt = $this->get('doctrine.orm.entity_manager')->getConnection()->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Warning, the values of the key "workflow_status" can change according to the user permissions.
     * They are currently set according to an user having "own" permission on every product.
     *
     * @return array
     */
    protected function getStandardizedProducts()
    {
        $standardizedProducts['product_viewable_by_everybody_1'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/products/product_viewable_by_everybody_1"
        }
    },
    "identifier": "product_viewable_by_everybody_1",
    "family": null,
    "parent": null,
    "groups": [],
    "categories": ["categoryA2"],
    "enabled": true,
    "values": {
        "a_localized_and_scopable_text_area": [
            { "data": "DE ecommerce", "locale": "de_DE", "scope": "tablet" },
            { "data": "EN ecommerce", "locale": "en_US", "scope": "ecommerce" },
            { "data": "FR ecommerce", "locale": "fr_FR", "scope": "tablet" }
        ],
        "a_multi_select": [
            {"locale":null,"scope":null,"data":["optionA","optionB"]}
        ],
        "a_number_float": [
            { "data": "12.0500", "locale": null, "scope": null }
        ],
        "a_localizable_image": [
            {
                "data": "3/3/6/a/336af1d213f9953530b3a7c4b4aeaf57615dbaaf_akeneo.jpg",
                "locale": "de_DE",
                "scope": null,
                "_links": {
                    "download": {
                        "href": "http://localhost/api/rest/v1/media-files/3/3/6/a/336af1d213f9953530b3a7c4b4aeaf57615dbaaf_akeneo.jpg"
                    }
                }
            },
            {
                "data": "3/3/6/a/336af1d213f9953530b3a7c4b4aeaf57615dbaaf_akeneo.jpg",
                "locale": "en_US",
                "scope": null,
                "_links": {
                    "download": {
                        "href": "http://localhost/api/rest/v1/media-files/3/3/6/a/336af1d213f9953530b3a7c4b4aeaf57615dbaaf_akeneo.jpg"
                    }
                }
            },
            {
                "data": "3/3/6/a/336af1d213f9953530b3a7c4b4aeaf57615dbaaf_akeneo.jpg",
                "locale": "fr_FR",
                "scope": null,
                "_links": {
                    "download": {
                        "href": "http://localhost/api/rest/v1/media-files/3/3/6/a/336af1d213f9953530b3a7c4b4aeaf57615dbaaf_akeneo.jpg"
                    }
                }
            }
        ],
        "a_metric_without_decimal_negative": [
            { "data": {"amount": -10, "unit": "CELSIUS"}, "locale": null, "scope": null }
        ]
    },
    "created": "2017-03-11T10:39:38+01:00",
    "updated": "2017-03-11T10:39:38+01:00",
    "associations": {
        "PACK": {
            "products": [],
            "product_models": [],
            "groups": []
        },
        "SUBSTITUTION": {
            "products": [],
            "product_models": [],
            "groups": []
        },
        "UPSELL": {
            "products": [],
            "product_models": [],
            "groups": []
        },
        "X_SELL": {
            "products": [],
            "product_models": [],
            "groups": []
        }
    },
    "metadata": {
        "workflow_status": "working_copy"
    }
}
JSON;

        $standardizedProducts['product_viewable_by_everybody_2'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/products/product_viewable_by_everybody_2"
        }
    },
    "identifier": "product_viewable_by_everybody_2",
    "family": null,
    "parent": null,
    "groups": [],
    "categories": ["categoryA2","categoryB"],
    "enabled": true,
    "values": {},
    "created": "2017-03-11T10:39:38+01:00",
    "updated": "2017-03-11T10:39:38+01:00",
    "associations": {
        "PACK": {
            "products": [],
            "product_models": [],
            "groups": []
        },
        "SUBSTITUTION": {
            "products": [],
            "product_models": [],
            "groups": []
        },
        "UPSELL": {
            "products": [],
            "product_models": [],
            "groups": []
        },
        "X_SELL": {
            "products": [],
            "product_models": [],
            "groups": []
        }
    },
    "metadata": {
        "workflow_status": "working_copy"
    }
}
JSON;

        $standardizedProducts['product_not_viewable_by_redactor'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/products/product_not_viewable_by_redactor"
        }
    },
    "identifier": "product_not_viewable_by_redactor",
    "family": null,
    "parent": null,
    "groups": [],
    "categories": ["categoryB"],
    "enabled": true,
    "values": {},
    "created": "2017-03-11T10:39:38+01:00",
    "updated": "2017-03-11T10:39:38+01:00",
    "associations": {
        "PACK": {
            "products": [],
            "product_models": [],
            "groups": []
        },
        "SUBSTITUTION": {
            "products": [],
            "product_models": [],
            "groups": []
        },
        "UPSELL": {
            "products": [],
            "product_models": [],
            "groups": []
        },
        "X_SELL": {
            "products": [],
            "product_models": [],
            "groups": []
        }
    },
    "metadata": {
        "workflow_status": "working_copy"
    }
}
JSON;

        $standardizedProducts['product_without_category'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/products/product_without_category"
        }
    },
    "identifier": "product_without_category",
    "family": null,
    "parent": null,
    "groups": [],
    "categories": [],
    "enabled": true,
    "values": {},
    "created": "2017-03-11T10:39:38+01:00",
    "updated": "2017-03-11T10:39:38+01:00",
    "associations": {
        "X_SELL": {
            "products": ["product_viewable_by_everybody_2", "product_not_viewable_by_redactor"],
            "groups": [],
            "product_models": []
        },
        "PACK": {
            "products": [],
            "groups": [],
            "product_models": []
        },
        "UPSELL": {
            "products": [],
            "groups": [],
            "product_models": []
        },
        "SUBSTITUTION": {
            "products": [],
            "groups": [],
            "product_models": []
        }
    },
    "metadata": {
        "workflow_status": "working_copy"
    }
}
JSON;

        return $standardizedProducts;
    }

    /**
     * @param Response $response
     * @param string   $message
     * @param string   $documentation
     */
    protected function assertError422(Response $response, $message, $documentation)
    {
        $expected = <<<JSON
{
  "code": 422,
  "message": "{$message}. Check the expected format on the API documentation.",
  "_links": {
    "documentation": {
      "href": "http://api.akeneo.com/api-reference.html#{$documentation}"
    }
  }
}
JSON;

        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString($expected, $response->getContent());
    }

    /**
     * @param array  $expectedProduct normalized data of the product that should be created
     * @param string $identifier      identifier of the product that should be created
     */
    protected function assertSameProducts(array $expectedProduct, $identifier)
    {
        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier($identifier);
        $standardizedProduct = $this->get('pim_standard_format_serializer')->normalize($product, 'standard');

        NormalizedProductCleaner::clean($standardizedProduct);
        NormalizedProductCleaner::clean($expectedProduct);

        $this->assertSame($expectedProduct, $standardizedProduct);
    }

    /**
     * @param array  $data
     * @param string $identifier
     */
    protected function updateProduct(array $data, $identifier)
    {
        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier($identifier);
        $this->get('pim_catalog.updater.product')->update($product, $data);
        $this->get('pim_catalog.saver.product')->save($product);
        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
    }

    protected function activateLocaleForChannel(string $localeCode, string $channelCode)
    {
        /** @var Locale $locale */
        $locale = $this->get('pim_catalog.repository.locale')->findOneByIdentifier($localeCode);

        /** @var Channel $channel */
        $channel = $this->get('pim_catalog.repository.channel')->findOneByIdentifier($channelCode);
        $channel->addLocale($locale);

        $this->get('pim_catalog.saver.channel')->save($channel);
    }

    protected function getProductUuidFromIdentifier(string $productIdentifier): UuidInterface
    {
        return Uuid::fromString($this->get('database_connection')->fetchOne(
            <<<SQL
WITH main_identifier AS (
    SELECT id
    FROM pim_catalog_attribute
    WHERE main_identifier = 1
    LIMIT 1
)
SELECT BIN_TO_UUID(product_uuid)
FROM pim_catalog_product_unique_data
WHERE raw_data = ?
AND attribute_id = (SELECT id FROM main_identifier)
SQL,
            [$productIdentifier]
        ));
    }
}
