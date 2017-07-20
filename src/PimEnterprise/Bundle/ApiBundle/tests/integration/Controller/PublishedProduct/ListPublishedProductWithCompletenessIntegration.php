<?php

namespace PimEnterprise\Bundle\ApiBundle\tests\integration\Controller\PublishedProduct;

use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class ListPublishedProductWithCompletenessIntegration extends AbstractPublishedProductTestCase
{
    /** @var Collection */
    private $products;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        parent::setUp();

        // product complete, whatever the scope
        $product1 = $this->createProduct('product_complete', [
            'family'     => 'familyA2',
            'categories' => ['categoryA', 'categoryB', 'master'],
            'values'     => [
                'a_metric' => [
                    ['data' => ['amount' => 1, 'unit' => 'WATT'], 'locale' => null, 'scope' => null]
                ],
                'a_number_float' => [
                    ['data' => '12.05', 'locale' => null, 'scope' => null]
                ]
            ]
        ]);

        // product complete only on en_US-tablet & en-US-ecommerce
        $product2 = $this->createProduct('product_complete_en_locale', [
            'family'     => 'familyA1',
            'categories' => ['categoryA', 'master', 'master_china'],
            'values'     => [
                'a_localizable_image' => [
                    ['data' => $this->getFixturePath('akeneo.jpg'), 'locale' => 'en_US', 'scope' => null],
                ],
                'a_date' => [
                    ['data' => '2016-06-28', 'locale' => null, 'scope' => null]
                ],
                'a_file' => [
                    ['data' => $this->getFixturePath('akeneo.txt'), 'locale' => null, 'scope' => null],
                ]
            ]
        ]);

        // product incomplete
        $product3 = $this->createProduct('product_incomplete', [
            'family'     => 'familyA',
            'categories' => ['categoryA', 'master', 'master_china'],
            'values'     => [
                'a_file' => [
                    ['data' => $this->getFixturePath('akeneo.txt'), 'locale' => null, 'scope' => null],
                ]
            ]
        ]);

        $this->products = $this->get('pim_catalog.repository.product')->findAll();
        $this->publishProduct($product1);
        $this->publishProduct($product2);
        $this->publishProduct($product3);

    }

    public function testPaginationWithCompletenessFilter()
    {
        $client = $this->createAuthenticatedClient([], [], null, null, 'mary', 'mary');

        $user = $this->get('pim_user.provider.user')->loadUserByUsername('mary');
        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->get('security.token_storage')->setToken($token);

        $encryptedId = urlencode($this->getEncryptedId('product_complete_en_locale'));
        $search = '{"completeness":[{"operator":"=","value":100,"scope":"ecommerce"}]}';
        $client->request('GET', 'api/rest/v1/published-products?scope=ecommerce&locales=en_US&limit=2&search=' . $search);
        $searchEncoded = urlencode($search);
        $expected = <<<JSON
{
    "_links": {
        "self": {"href": "http://localhost/api/rest/v1/published-products?limit=2&scope=ecommerce&locales=en_US&search=${searchEncoded}"},
        "first": {"href": "http://localhost/api/rest/v1/published-products?limit=2&scope=ecommerce&locales=en_US&search=${searchEncoded}"},
        "next": {"href": "http://localhost/api/rest/v1/published-products?limit=2&scope=ecommerce&locales=en_US&search=${searchEncoded}&search_after=${encryptedId}"},
    },
    "current_page" : null,
    "_embedded": {
        "items": [
          {
            "_links": {
              "self": {
                "href": "http:\/\/localhost\/api\/rest\/v1\/published-products\/product_complete"
              }
            },
            "identifier": "product_complete",
            "family": "familyA2",
            "groups": [
            ],
            "variant_group": null,
            "categories": [
              "categoryA",
              "categoryB",
              "master"
            ],
            "enabled": true,
            "values": {
              "a_metric": [
                {
                  "locale": null,
                  "scope": null,
                  "data": {
                    "amount": "1.0000",
                    "unit": "WATT"
                  }
                }
              ],
              "a_number_float": [
                {
                  "locale": null,
                  "scope": null,
                  "data": "12.0500"
                }
              ]
            },
            "created": "2017-07-20T10:42:03+02:00",
            "updated": "2017-07-20T10:42:03+02:00",
            "associations": {
              
            }
          },
          {
            "_links": {
              "self": {
                "href": "http:\/\/localhost\/api\/rest\/v1\/published-products\/product_complete_en_locale"
              }
            },
            "identifier": "product_complete_en_locale",
            "family": "familyA1",
            "groups": [
              
            ],
            "variant_group": null,
            "categories": [
              "categoryA",
              "master",
              "master_china"
            ],
            "enabled": true,
            "values": {
              "a_date": [
                {
                  "locale": null,
                  "scope": null,
                  "data": "2016-06-28T00:00:00+02:00"
                }
              ],
              "a_file": [
                {
                  "locale": null,
                  "scope": null,
                  "data": "c\/9\/7\/6\/c976c1363be5b7b12b11e034942eda9d0d884b4b_akeneo.txt",
                  "_links": {
                    "download": {
                      "href": "hhttp:\/\/localhost\/api\/rest\/v1\/media-files\/c\/9\/7\/6\/c976c1363be5b7b12b11e034942eda9d0d884b4b_akeneo.txt\/download"
                    }
                  }
                }
              ],
              "a_localizable_image": [
                {
                  "locale": "en_US",
                  "scope": null,
                  "data": "9\/c\/1\/7\/9c170316e10c590e1ba5e22774f22da936b623ee_akeneo.jpg",
                  "_links": {
                    "download": {
                      "href": "http:\/\/localhost\/api\/rest\/v1\/media-files\/9\/c\/1\/7\/9c170316e10c590e1ba5e22774f22da936b623ee_akeneo.jpg\/download"
                    }
                  }
                }
              ]
            },
            "created": "2017-07-20T10:42:03+02:00",
            "updated": "2017-07-20T10:42:03+02:00",
            "associations": {
            }
          }
        ]
      }
    }
JSON;

        $this->assertListResponse($client->getResponse(), $expected);
    }

    /**
     * @param string $productIdentifier
     */
    private function getEncryptedId($productIdentifier)
    {
        $encrypter = $this->get('pim_api.security.primary_key_encrypter');
        $productRepository = $this->get('pim_catalog.repository.product');

        $product = $productRepository->findOneByIdentifier($productIdentifier);

        return $encrypter->encrypt($product->getId());
    }
}
