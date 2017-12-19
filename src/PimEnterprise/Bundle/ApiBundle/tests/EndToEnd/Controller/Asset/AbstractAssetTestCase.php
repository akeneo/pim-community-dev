<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ApiBundle\tests\EndToEnd\Controller\Asset;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\IntegrationTestsBundle\Sanitizer\DateSanitizer;
use Pim\Bundle\ApiBundle\tests\integration\ApiTestCase;
use PimEnterprise\Component\ProductAsset\FileStorage;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use PimEnterprise\Component\ProductAsset\Model\TagInterface;

/**
 * @author Damien Carcel <damien.carcel@akeneo.com>
 */
abstract class AbstractAssetTestCase extends ApiTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->createTag('akeneo');

        $this->createAsset([
            'code' => 'non_localizable_asset',
            'localized' => false,
            'description' => 'A very useful description.',
            'end_of_use' => '2041-01-01T00:00:00',
            'categories' => [
                'asset_main_catalog',
            ],
            'tags' => [
                'akeneo',
            ],
        ]);

        $this->createAsset([
            'code' => 'localizable_asset',
            'localized' => true,
            'description' => 'Another useful description.',
            'end_of_use' => '2041-01-01T00:00:00',
            'categories' => [
                'asset_main_catalog',
            ],
            'tags' => [
                'akeneo',
            ],
        ]);

        $this->createAssetWithoutReferences([
            'code' => 'localizable_asset_without_references',
            'localized' => true,
            'description' => 'Another useful description.',
            'end_of_use' => '2041-01-01T00:00:00',
            'categories' => [
                'asset_main_catalog',
            ],
            'tags' => [
                'akeneo',
            ],
        ]);

        $this->createAssetWithoutReferences([
            'code' => 'non_localizable_asset_without_references',
            'localized' => false,
            'description' => 'Another useful description.',
            'end_of_use' => '2041-01-01T00:00:00',
            'categories' => [
                'asset_main_catalog',
            ],
            'tags' => [
                'akeneo',
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }

    /**
     * @return string[]
     */
    protected function getStandardizedAssets(): array
    {
        $ecommerceChannel = $this->get('pim_api.repository.channel')->findOneByIdentifier('ecommerce');
        $ecommerceChinaChannel = $this->get('pim_api.repository.channel')->findOneByIdentifier('ecommerce_china');
        $tabletChannel = $this->get('pim_api.repository.channel')->findOneByIdentifier('tablet');

        $nonLocalizableAsset = $this->get('pimee_api.repository.asset')->findOneByIdentifier('non_localizable_asset');
        $nonLocalizableReference = $nonLocalizableAsset->getReference()->getFileInfo()->getKey();
        $nonLocalizableVariationEcommerce = $nonLocalizableAsset
            ->getVariation($ecommerceChannel)
            ->getFileInfo()
            ->getKey();
        $nonLocalizableVariationEcommerceChina = $nonLocalizableAsset
            ->getVariation($ecommerceChinaChannel)
            ->getFileInfo()
            ->getKey();
        $nonLocalizableVariationTablet = $nonLocalizableAsset
            ->getVariation($tabletChannel)
            ->getFileInfo()
            ->getKey();

        $assets = [];

        $assets['non_localizable_asset'] = <<<JSON
{
  "code": "non_localizable_asset",
  "categories": ["asset_main_catalog"],
  "description": "A very useful description.",
  "localized": false,
  "tags": ["akeneo"],
  "end_of_use": "2041-01-01T00:00:00+01:00",
  "variation_files": [
    {
      "_link": {
        "download": {
          "href": "http://localhost/api/rest/v1/assets/non_localizable_asset/variation-files/ecommerce/no-locale/download"
        },
        "self": {
          "href": "http://localhost/api/rest/v1/assets/non_localizable_asset/variation-files/ecommerce/no-locale"
        }
      },
      "locale":null,
      "channel": "ecommerce",
      "code": "$nonLocalizableVariationEcommerce"
    },
    {
      "_link": {
        "download": {
          "href": "http://localhost/api/rest/v1/assets/non_localizable_asset/variation-files/tablet/no-locale/download"
        },
        "self": {
          "href": "http://localhost/api/rest/v1/assets/non_localizable_asset/variation-files/tablet/no-locale"
        }
      },
      "locale":null,
      "channel": "tablet",
      "code": "$nonLocalizableVariationTablet"
    },
    {
      "_link": {
        "download": {
          "href": "http://localhost/api/rest/v1/assets/non_localizable_asset/variation-files/ecommerce_china/no-locale/download"
        },
        "self": {
          "href": "http://localhost/api/rest/v1/assets/non_localizable_asset/variation-files/ecommerce_china/no-locale"
        }
      },
      "locale":null,
      "channel": "ecommerce_china",
      "code": "$nonLocalizableVariationEcommerceChina"
    }
  ],
  "reference_files":[
    {
      "_link": {
        "download": {
          "href": "http://localhost/api/rest/v1/assets/non_localizable_asset/reference-files/no-locale/download"
        },
        "self": {
          "href": "http://localhost/api/rest/v1/assets/non_localizable_asset/reference-files/no-locale"
        }
      },
      "locale": null,
      "code": "$nonLocalizableReference"
    }
  ]
}
JSON;

        $chinese = $this->get('pim_api.repository.locale')->findOneByIdentifier('zh_CN');
        $english = $this->get('pim_api.repository.locale')->findOneByIdentifier('en_US');
        $french = $this->get('pim_api.repository.locale')->findOneByIdentifier('fr_FR');
        $german = $this->get('pim_api.repository.locale')->findOneByIdentifier('de_DE');

        $localizableAsset = $this->get('pimee_api.repository.asset')->findOneByIdentifier('localizable_asset');

        $localizableReferenceChinese = $localizableAsset->getReference($chinese)->getFileInfo()->getKey();
        $localizableReferenceEnglish = $localizableAsset->getReference($english)->getFileInfo()->getKey();
        $localizableReferenceFrench = $localizableAsset->getReference($french)->getFileInfo()->getKey();
        $localizableReferenceGerman = $localizableAsset->getReference($german)->getFileInfo()->getKey();

        $localizableVariationEcommerceEnglish = $localizableAsset
            ->getVariation($ecommerceChannel, $english)
            ->getFileInfo()
            ->getKey();
        $localizableVariationEcommerceChinaEnglish = $localizableAsset
            ->getVariation($ecommerceChinaChannel, $english)
            ->getFileInfo()
            ->getKey();
        $localizableVariationEcommerceChinaChinese = $localizableAsset
            ->getVariation($ecommerceChinaChannel, $chinese)
            ->getFileInfo()
            ->getKey();
        $localizableVariationTabletEnglish = $localizableAsset
            ->getVariation($tabletChannel, $english)
            ->getFileInfo()
            ->getKey();
        $localizableVariationTabletFrench = $localizableAsset
            ->getVariation($tabletChannel, $french)
            ->getFileInfo()
            ->getKey();
        $localizableVariationTabletGerman = $localizableAsset
            ->getVariation($tabletChannel, $german)
            ->getFileInfo()
            ->getKey();

        $assets['localizable_asset'] = <<<JSON
{
  "code": "localizable_asset",
  "categories": ["asset_main_catalog"],
  "description": "Another useful description.",
  "localized": true,
  "tags": ["akeneo"],
  "end_of_use": "2041-01-01T00:00:00+01:00",
  "variation_files": [
    {
      "_link": {
        "download": {
          "href": "http://localhost/api/rest/v1/assets/localizable_asset/variation-files/tablet/de_DE/download"
        },
        "self": {
          "href": "http://localhost/api/rest/v1/assets/localizable_asset/variation-files/tablet/de_DE"
        }
      },
      "locale": "de_DE",
      "channel": "tablet",
      "code": "$localizableVariationTabletGerman"
    },
    {
      "_link": {
        "download": {
          "href": "http://localhost/api/rest/v1/assets/localizable_asset/variation-files/ecommerce/en_US/download"
        },
        "self": {
          "href": "http://localhost/api/rest/v1/assets/localizable_asset/variation-files/ecommerce/en_US"
        }
      },
      "locale": "en_US",
      "channel": "ecommerce",
      "code": "$localizableVariationEcommerceEnglish"
    },
    {
      "_link": {
        "download": {
          "href": "http://localhost/api/rest/v1/assets/localizable_asset/variation-files/tablet/en_US/download"
        },
        "self": {
          "href": "http://localhost/api/rest/v1/assets/localizable_asset/variation-files/tablet/en_US"
        }
      },
      "locale": "en_US",
      "channel": "tablet",
      "code": "$localizableVariationTabletEnglish"
    },
    {
      "_link": {
        "download": {
          "href": "http://localhost/api/rest/v1/assets/localizable_asset/variation-files/ecommerce_china/en_US/download"
        },
        "self": {
          "href": "http://localhost/api/rest/v1/assets/localizable_asset/variation-files/ecommerce_china/en_US"
        }
      },
      "locale": "en_US",
      "channel": "ecommerce_china",
      "code": "$localizableVariationEcommerceChinaEnglish"
    },
    {
      "_link": {
        "download": {
          "href": "http://localhost/api/rest/v1/assets/localizable_asset/variation-files/tablet/fr_FR/download"
        },
        "self": {
          "href": "http://localhost/api/rest/v1/assets/localizable_asset/variation-files/tablet/fr_FR"
        }
      },
      "locale": "fr_FR",
      "channel": "tablet",
      "code": "$localizableVariationTabletFrench"
    },
    {
      "_link": {
        "download": {
          "href": "http://localhost/api/rest/v1/assets/localizable_asset/variation-files/ecommerce_china/zh_CN/download"
        },
        "self": {
          "href": "http://localhost/api/rest/v1/assets/localizable_asset/variation-files/ecommerce_china/zh_CN"
        }
      },
      "locale": "zh_CN",
      "channel": "ecommerce_china",
      "code": "$localizableVariationEcommerceChinaChinese"
    }
  ],
  "reference_files":[
    {
      "_link": {
        "download": {
          "href": "http://localhost/api/rest/v1/assets/localizable_asset/reference-files/de_DE/download"
        },
        "self": {
          "href": "http://localhost/api/rest/v1/assets/localizable_asset/reference-files/de_DE"
        }
      },
      "locale": "de_DE",
      "code": "$localizableReferenceGerman"
    },
    {
      "_link": {
        "download": {
          "href": "http://localhost/api/rest/v1/assets/localizable_asset/reference-files/en_US/download"
        },
        "self": {
          "href": "http://localhost/api/rest/v1/assets/localizable_asset/reference-files/en_US"
        }
      },
      "locale": "en_US",
      "code": "$localizableReferenceEnglish"
    },
    {
      "_link": {
        "download": {
          "href": "http://localhost/api/rest/v1/assets/localizable_asset/reference-files/fr_FR/download"
        },
        "self": {
          "href": "http://localhost/api/rest/v1/assets/localizable_asset/reference-files/fr_FR"
        }
      },
      "locale": "fr_FR",
      "code": "$localizableReferenceFrench"
    },
    {
      "_link": {
        "download": {
          "href": "http://localhost/api/rest/v1/assets/localizable_asset/reference-files/zh_CN/download"
        },
        "self": {
          "href": "http://localhost/api/rest/v1/assets/localizable_asset/reference-files/zh_CN"
        }
      },
      "locale": "zh_CN",
      "code": "$localizableReferenceChinese"
    }
  ]
}
JSON;

        $assets['localizable_asset_without_references'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/assets/localizable_asset_without_references"
        }
    },
    "code": "localizable_asset_without_references",
    "localized": true,
    "description": "Another useful description.",
    "end_of_use": "2041-01-01T00:00:00+01:00",
    "tags": ["akeneo"],
    "categories": ["asset_main_catalog"],
    "variation_files": [],
    "reference_files": []
}
JSON;

        $assets['non_localizable_asset_without_references'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/assets/non_localizable_asset_without_references"
        }
    },
    "code": "non_localizable_asset_without_references",
    "localized": false,
    "description": "Another useful description.",
    "end_of_use": "2041-01-01T00:00:00+01:00",
    "tags": ["akeneo"],
    "categories": ["asset_main_catalog"],
    "variation_files": [],
    "reference_files": []
}
JSON;

        $assets['cat'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/assets/cat"
        }
    },
    "code": "cat",
    "localized": true,
    "description": null,
    "end_of_use": "2041-04-02T00:00:00+01:00",
    "tags": ["animal"],
    "categories": ["asset_main_catalog"],
    "variation_files": [],
    "reference_files": []
}
JSON;

        $assets['dog'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/assets/dog"
        }
    },
    "code": "dog",
    "localized": false,
    "description": null,
    "end_of_use": null,
    "tags": [],
    "categories": [],
    "variation_files": [],
    "reference_files": []
}
JSON;

        return $assets;
    }

    /**
     * @param array $asset
     *
     * @return array
     */
    protected function sanitizeNormalizedAsset(array $asset): array
    {
        $asset['end_of_use'] = DateSanitizer::sanitize($asset['end_of_use']);

        ksort($asset);

        return $asset;
    }

    /**
     * @param string $code
     *
     * @throws \PHPUnit_Framework_AssertionFailedError
     *
     * @return TagInterface
     */
    protected function createTag(string $code): TagInterface
    {
        $tag = $this->get('pimee_product_asset.factory.tag')->create();
        $this->get('pimee_product_asset.updater.tag')->update($tag, ['code' => $code]);

        $errors = $this->get('validator')->validate($tag);
        $this->assertCount(0, $errors);

        $this->get('pimee_product_asset.saver.tag')->save($tag);

        return $tag;
    }

    /**
     * Creates an asset with data.
     *
     * @param array $data
     *
     * @throws \Exception
     *
     * @return AssetInterface
     */
    private function createAsset(array $data): AssetInterface
    {
        $asset = $this->get('pimee_product_asset.factory.asset')->create();

        $this->get('pimee_product_asset.updater.asset')->update($asset, $data);

        foreach ($asset->getReferences() as $reference) {
            $fileInfo = new \SplFileInfo($this->getFixturePath('ziggy.png'));
            $storedFile = $this->get('akeneo_file_storage.file_storage.file.file_storer')->store(
                $fileInfo,
                FileStorage::ASSET_STORAGE_ALIAS
            );

            $reference->setFileInfo($storedFile);
            $this->get('pimee_product_asset.updater.files')->resetAllVariationsFiles($reference, true);
        }

        $errors = $this->get('validator')->validate($asset);
        $this->assertCount(0, $errors);

        $this->get('pimee_product_asset.saver.asset')->save($asset);

        $this->get('pimee_product_asset.variations_collection_files_generator')->generate(
            $asset->getVariations(),
            true
        );

        return $asset;
    }

    /**
     * Creates an asset with data but no references.
     *
     * @param array $data
     *
     * @return AssetInterface
     */
    private function createAssetWithoutReferences(array $data): AssetInterface
    {
        $asset = $this->get('pimee_product_asset.factory.asset')->create();

        $this->get('pimee_product_asset.updater.asset')->update($asset, $data);

        $errors = $this->get('validator')->validate($asset);
        $this->assertCount(0, $errors);

        $this->get('pimee_product_asset.saver.asset')->save($asset);

        return $asset;
    }
}
