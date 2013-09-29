<?php

namespace Oro\Bundle\AddressBundle\Tests\Unit\ImportExport\Serializer\Normalizer;

use Oro\Bundle\AddressBundle\ImportExport\Serializer\Normalizer\EmailNormalizer;
use Oro\Bundle\AddressBundle\Tests\Unit\ImportExport\Serializer\Normalizer\Stub\StubEmail;

class EmailNormalizerTest extends \PHPUnit_Framework_TestCase
{
    const EMAIL_TYPE = 'Oro\Bundle\AddressBundle\Tests\Unit\ImportExport\Serializer\Normalizer\Stub\StubEmail';

    /**
     * @var EmailNormalizer
     */
    protected $normalizer;

    protected function setUp()
    {
        $this->normalizer = new EmailNormalizer();
    }

    public function testSupportsNormalization()
    {
        $this->assertFalse($this->normalizer->supportsNormalization(array()));
        $this->assertTrue($this->normalizer->supportsNormalization($this->createEmail()));
    }

    public function testSupportsDenormalization()
    {
        $this->assertFalse($this->normalizer->supportsDenormalization(array(), 'stdClass'));
        $this->assertFalse($this->normalizer->supportsDenormalization(array(), self::EMAIL_TYPE));
        $this->assertFalse(
            $this->normalizer->supportsDenormalization(
                'email@example.com',
                EmailNormalizer::ABSTRACT_EMAIL_TYPE
            )
        );
        $this->assertTrue($this->normalizer->supportsDenormalization('email@example.com', self::EMAIL_TYPE));
    }

    public function testNormalize()
    {
        $this->assertEquals(
            'email@example.com',
            $this->normalizer->normalize($this->createEmail()->setEmail('email@example.com'), null)
        );
    }

    public function testDenormalize()
    {
        $result = $this->normalizer->denormalize('email@example.com', self::EMAIL_TYPE);

        $this->assertInstanceOf(self::EMAIL_TYPE, $result);
        $this->assertEquals('email@example.com', $result->getEmail());
    }

    protected function createEmail()
    {
        return new StubEmail();
    }
}
