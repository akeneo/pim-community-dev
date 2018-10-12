<?php

namespace AkeneoTest\Channel\Integration\Component\Normalizer\Standard;

use Akeneo\Test\Integration\TestCase;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CurrencyIntegration extends TestCase
{
    public function testCurrency()
    {
        $expected = [
            'code'    => 'USD',
            'enabled' => true,
        ];

        $repository = $this->get('pim_catalog.repository.currency');
        $serializer = $this->get('pim_standard_format_serializer');

        $result = $serializer->normalize($repository->findOneByIdentifier('USD'), 'standard');

        $this->assertSame($expected, $result);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
