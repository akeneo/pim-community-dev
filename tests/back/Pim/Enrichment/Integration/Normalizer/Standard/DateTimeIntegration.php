<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Normalizer\Standard;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DateTimeIntegration extends AbstractStandardNormalizerTestCase
{
    public function testDateTimeWithParisTimezone()
    {
        $datetime = new \DateTime('2015-01-01 23:50:00');
        $timezone = new \DateTimeZone('Europe/Paris');
        $datetime->setTimezone($timezone);

        $serializer = $this->get('pim_standard_format_serializer');
        $result = $serializer->normalize($datetime, 'standard');

        $this->assertSame('2015-01-01T23:50:00+01:00', $result);
    }

    public function testDateTimeWithNewYorkTimezone()
    {
        $datetime = new \DateTime('2015-01-01');
        $timezone = new \DateTimeZone('America/New_York');
        $datetime->setTimezone($timezone);

        $serializer = $this->get('pim_standard_format_serializer');
        $result = $serializer->normalize($datetime, 'standard');

        $this->assertSame('2014-12-31T18:00:00-05:00', $result);
    }
}
