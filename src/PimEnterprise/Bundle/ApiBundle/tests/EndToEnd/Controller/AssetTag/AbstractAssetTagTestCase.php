<?php

namespace PimEnterprise\Bundle\ApiBundle\tests\EndToEnd\Controller\AssetTag;

use Akeneo\Component\Classification\Model\TagInterface;
use Akeneo\Test\Integration\Configuration;
use Pim\Bundle\ApiBundle\tests\integration\ApiTestCase;

/**
 * @author Philippe Mossière <philippe.mossiere@akeneo.com>
 */
class AbstractAssetTagTestCase extends ApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createTag('akeneo');
        $this->createTag('full_hd');
        $this->createTag('popeye');
        $this->createTag('thumbnail');
        $this->createTag('view');
    }

    protected function getStandardizedAssetTags()
    {
        $tags = [];

        $tags['akeneo'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/asset-tags/akeneo"
        }
    },
    "code": "akeneo"
}
JSON;
        $tags['animal'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/asset-tags/animal"
        }
    },
    "code": "animal"
}
JSON;
        $tags['full_hd'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/asset-tags/full_hd"
        }
    },
    "code": "full_hd"
}
JSON;
        $tags['popeye'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/asset-tags/popeye"
        }
    },
    "code": "popeye"
}
JSON;
        $tags['thumbnail'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/asset-tags/thumbnail"
        }
    },
    "code": "thumbnail"
}
JSON;
        $tags['view'] = <<<JSON
{
    "_links": {
        "self": {
            "href": "http://localhost/api/rest/v1/asset-tags/view"
        }
    },
    "code": "view"
}
JSON;

        return $tags;
    }

    /**
     * @param string $code
     *
     * @return TagInterface
     */
    private function createTag(string $code): TagInterface
    {
        $tag = $this->get('pimee_product_asset.factory.tag')->create();
        $this->get('pimee_product_asset.updater.tag')->update($tag, ['code' => $code]);

        $errors = $this->get('validator')->validate($tag);
        $this->assertCount(0, $errors);

        $this->get('pimee_product_asset.saver.tag')->save($tag);

        return $tag;
    }

    /**
     * @return Configuration
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
