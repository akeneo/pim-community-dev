<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Normalizer\Standard;

use Akeneo\Test\IntegrationTestsBundle\Sanitizer\MediaSanitizer;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileIntegration extends AbstractStandardNormalizerTestCase
{
    public function testNormalizedFile()
    {
        $fileStorer = $this->get('akeneo_file_storage.file_storage.file.file_storer');
        $fileStorer->store(new \SplFileInfo($this->getFixturePath('akeneo.jpg')), 'catalogStorage');

        $expected = [
            'code'              => '1/8/c/d/18cd06901ca3bd633afc0c178e347ffba20cbd11_akeneo.jpg',
            'original_filename' => 'akeneo.jpg',
            'mime_type'         => 'image/jpeg',
            'size'              => 10584,
            'extension'         => 'jpg'
        ];

        $this->assert('akeneo.jpg', $expected);
    }

    /**
     * @param string $name
     * @param array  $expected
     */
    private function assert($name, array $expected)
    {
        $repository = $this->get('akeneo_file_storage.repository.file_info');
        $serializer = $this->get('pim_standard_format_serializer');

        $result = $serializer->normalize($repository->findOneBy(['originalFilename' => $name]), 'standard');

        $result['code'] = MediaSanitizer::sanitize($result['code']);
        $expected['code'] = MediaSanitizer::sanitize($expected['code']);

        $this->assertSame($expected, $result);
    }
}
