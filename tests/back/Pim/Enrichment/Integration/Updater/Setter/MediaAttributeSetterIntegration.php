<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Updater\Setter;

use Akeneo\Pim\Enrichment\Product\API\Command\UpsertProductCommand;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\SetImageValue;
use Akeneo\Pim\Enrichment\Product\API\Command\UserIntent\UserIntent;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Test\IntegrationTestsBundle\Sanitizer\MediaSanitizer;

/**
 * @author    Alexandre Hocquard <alexandre.hocquard@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class MediaAttributeSetterIntegration extends TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }

    public function testLocalizableMedia()
    {
        $attributeName = 'a_localizable_image';

        $userIntents = [
            new SetImageValue(
                $attributeName,
                null,
                'fr_FR',
                $this->getFileInfoKey($this->getParameter('kernel.project_dir').'/tests/legacy/features/Context/fixtures/SNKRS-1R.png'))
        ];

        $result = [
            [
                'locale' => 'fr_FR',
                'scope'  => null,
                'data'   => 'd/5/e/1/d5e1aeb5149a8a721e567952c895d20ffef8c6d9_SNKRS_1R.png',
            ],
        ];

        $this->assertCommandMedia($userIntents, $result, $attributeName);
    }

    public function testMediaWithChannel()
    {
        $attributeName = 'a_scopable_image';

        $userIntents = [
            new SetImageValue(
                $attributeName,
                'tablet',
                null,
                $this->getFileInfoKey($this->getParameter('kernel.project_dir').'/tests/legacy/features/Context/fixtures/SNKRS-1C-t.png')
            )
        ];

        $result = [
            [
                'locale' => null,
                'scope'  => 'tablet',
                'data'   => 'd/5/e/1/d5e1aeb5149a8a721e567952c895d20ffef8c6d9_SNKRS_1R.png',
            ],
        ];

        $this->assertCommandMedia($userIntents, $result, $attributeName);
    }

    public function testMediaWithLocaleAndChannel()
    {
        $attributeName = 'a_localizable_scopable_image';

        $userIntents = [
            new SetImageValue(
                $attributeName,
                'tablet',
                'fr_FR',
                $this->getFileInfoKey($this->getParameter('kernel.project_dir').'/tests/legacy/features/Context/fixtures/SNKRS-1R.png')
            )
        ];

        $result = [
            [
                'locale' => 'fr_FR',
                'scope'  => 'tablet',
                'data'   => 'd/5/e/1/d5e1aeb5149a8a721e567952c895d20ffef8c6d9_SNKRS_1R.png',
            ],
        ];

        $this->assertCommandMedia($userIntents, $result, $attributeName);
    }

    public function testIsSameMedia()
    {
        $attributeName = 'a_localizable_scopable_image';

        $userIntents = [
            new SetImageValue($attributeName, 'tablet', 'fr_FR', $this->getFileInfoKey($this->getParameter('kernel.project_dir').'/tests/legacy/features/Context/fixtures/SNKRS-1R.png')),
            new SetImageValue($attributeName, 'tablet', 'fr_FR', $this->getFileInfoKey($this->getParameter('kernel.project_dir').'/tests/legacy/features/Context/fixtures/SNKRS-1R.png')),
        ];

        $result = [
            [
                'locale' => 'fr_FR',
                'scope'  => 'tablet',
                'data'   => 'd/5/e/1/d5e1aeb5149a8a721e567952c895d20ffef8c6d9_SNKRS_1R.png',
            ],
        ];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The value for attribute a_localizable_scopable_image is being updated multiple times');

        $this->assertCommandMedia($userIntents, $result, $attributeName);
    }

    /**
     * @param UserIntent[] $userIntents
     */
    protected function assertCommandMedia(array $userIntents, array $result, string $attributeName)
    {
        $this->get('akeneo_integration_tests.helper.authenticator')->logIn('admin');
        $command = UpsertProductCommand::createFromCollection(
            userId: $this->getUserId('admin'),
            productIdentifier: 'product_media',
            userIntents: $userIntents
        );
        $this->get('pim_enrich.product.message_bus')->dispatch($command);
        $product = $this->get('pim_catalog.repository.product')->findOneByIdentifier('product_media');

        $standardProduct = $this->get('pim_standard_format_serializer')->normalize($product, 'standard');

        $result = $this->sanitizeMediaAttributeData($result);
        $standardValues = $this->sanitizeMediaAttributeData($standardProduct['values'][$attributeName]);

        $this->assertEquals($result, $standardValues);
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
        foreach ($data as $index => $value) {
            $data[$index]['data'] = MediaSanitizer::sanitize($value['data']);
        }

        return $data;
    }
}
