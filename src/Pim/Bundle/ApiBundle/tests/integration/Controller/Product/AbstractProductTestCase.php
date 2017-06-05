<?php

namespace Pim\Bundle\ApiBundle\tests\integration\Controller\Product;

use Akeneo\Test\Integration\DateSanitizer;
use Akeneo\Test\Integration\MediaSanitizer;
use Pim\Bundle\ApiBundle\tests\integration\ApiTestCase;
use Pim\Component\Catalog\Model\ProductInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractProductTestCase extends ApiTestCase
{
    /**
     * @param string $identifier
     * @param array  $data
     *
     * @return ProductInterface
     */
    protected function createProduct($identifier, array $data = [])
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier);
        $this->get('pim_catalog.updater.product')->update($product, $data);
        $this->get('pim_catalog.saver.product')->save($product);

        $this->get('akeneo_elasticsearch.client')->refreshIndex();

        return $product;
    }

    /**
     * Replaces dates fields (created/updated) in the $data array by self::DATE_FIELD_COMPARISON.
     *
     * @param array $data
     *
     * @return array
     */
    protected function sanitizeDateFields(array $data)
    {
        if (isset($data['created'])) {
            $data['created'] = DateSanitizer::sanitize($data['created']);
        }
        if (isset($data['updated'])) {
            $data['updated'] = DateSanitizer::sanitize($data['updated']);
        }

        return $data;
    }

    /**
     * Replaces media attributes data in the $data array by self::MEDIA_ATTRIBUTE_DATA_COMPARISON.
     *
     * @param array $data
     *
     * @return array
     */
    protected function sanitizeMediaAttributeData(array $data)
    {
        if (!isset($data['values'])) {
            return $data;
        }

        foreach ($data['values'] as $attributeCode => $values) {
            if (1 === preg_match('/.*(file|image).*/', $attributeCode)) {
                foreach ($values as $index => $value) {
                    $sanitizedData = ['data' => MediaSanitizer::sanitize($value['data'])];
                    if (isset($value['_links']['download']['href'])) {
                        $sanitizedData['_links']['download']['href'] = MediaSanitizer::sanitize(
                            $value['_links']['download']['href']
                        );
                    }

                    $data['values'][$attributeCode][$index] = array_replace($value, $sanitizedData);
                }
            }
        }

        return $data;
    }

    /**
     * @param Response $response
     * @param string   $expected
     */
    protected function assertListResponse(Response $response, $expected)
    {
        $result = json_decode($response->getContent(), true);
        $expected = json_decode($expected, true);

        foreach ($result['_embedded']['items'] as $index => $product) {
            $product = $this->sanitizeDateFields($product);
            $result['_embedded']['items'][$index] = $this->sanitizeMediaAttributeData($product);

            if (isset($expected['_embedded']['items'][$index])) {
                $expected['_embedded']['items'][$index] = $this->sanitizeDateFields($expected['_embedded']['items'][$index]);
                $expected['_embedded']['items'][$index] = $this->sanitizeMediaAttributeData($expected['_embedded']['items'][$index]);
            }
        }

        $this->assertEquals($expected, $result);
    }
}
