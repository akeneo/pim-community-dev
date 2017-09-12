<?php

declare(strict_types=1);

namespace PimEnterprise\Bundle\ApiBundle\tests\integration\Controller\PublishedProduct;

use Pim\Component\Catalog\Model\ProductInterface;
use PimEnterprise\Bundle\ApiBundle\tests\integration\Controller\Product\AbstractProductTestCase;
use PimEnterprise\Component\Workflow\Model\PublishedProductInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
abstract class AbstractPublishedProductTestCase extends AbstractProductTestCase
{
    /**
     * @param ProductInterface $product
     *
     * @return PublishedProductInterface
     */
    protected function publishProduct(ProductInterface $product): PublishedProductInterface
    {
        $published = $this->get('pimee_workflow.manager.published_product')->publish($product);

        $this->get('akeneo_elasticsearch.client.product')->refreshIndex();

        return $published;
    }

    /**
     * @return array
     */
    protected function getStandardizedPublishedProducts(): array
    {
        $standardizedPublishedProducts['product_viewable_by_everybody_1'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/products/product_viewable_by_everybody_1"
        }
    },
    "identifier": "product_viewable_by_everybody_1",
    "family": null,
    "groups": [],
    "categories": ["categoryA2"],
    "enabled": true,
    "values": {
        "a_localized_and_scopable_text_area": [
            { "data": "DE ecommerce", "locale": "de_DE", "scope": "ecommerce" },
            { "data": "EN ecommerce", "locale": "en_US", "scope": "ecommerce" },
            { "data": "FR ecommerce", "locale": "fr_FR", "scope": "ecommerce" }
        ],
        "a_multi_select": [
            {"locale":null,"scope":null,"data":["optionA","optionB"]}
        ],
        "a_number_float": [
            { "data": "12.05", "locale": null, "scope": null }
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
    "associations": {}
}
JSON;

        $standardizedPublishedProducts['product_viewable_by_everybody_2'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/products/product_viewable_by_everybody_2"
        }
    },
    "identifier": "product_viewable_by_everybody_2",
    "family": null,
    "groups": [],
    "categories": ["categoryA2","categoryB"],
    "enabled": true,
    "values": {},
    "created": "2017-03-11T10:39:38+01:00",
    "updated": "2017-03-11T10:39:38+01:00",
    "associations": {}
}
JSON;

        $standardizedPublishedProducts['product_not_viewable_by_redactor'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/products/product_not_viewable_by_redactor"
        }
    },
    "identifier": "product_not_viewable_by_redactor",
    "family": null,
    "groups": [],
    "categories": ["categoryB"],
    "enabled": true,
    "values": {},
    "created": "2017-03-11T10:39:38+01:00",
    "updated": "2017-03-11T10:39:38+01:00",
    "associations": {}
}
JSON;

        $standardizedPublishedProducts['product_without_category'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/products/product_without_category"
        }
    },
    "identifier": "product_without_category",
    "family": null,
    "groups": [],
    "categories": [],
    "enabled": true,
    "values": {},
    "created": "2017-03-11T10:39:38+01:00",
    "updated": "2017-03-11T10:39:38+01:00",
    "associations": {
        "X_SELL": {
            "products": ["product_viewable_by_everybody_2", "product_not_viewable_by_redactor"],
            "groups": []
        }
    }
}
JSON;

        return $standardizedPublishedProducts;
    }

    /**
     * @param string $publishedProductIdentifier
     *
     * @return string
     */
    protected function getEncryptedId(string $publishedProductIdentifier): string
    {
        $user = $this->get('pim_user.provider.user')->loadUserByUsername('admin');
        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->get('security.token_storage')->setToken($token);

        $encrypter = $this->get('pim_api.security.primary_key_encrypter');
        $publishedProductRepository = $this->get('pimee_api.repository.published_product');

        $product = $publishedProductRepository->findOneByIdentifier($publishedProductIdentifier);

        return $encrypter->encrypt($product->getId());
    }
}
