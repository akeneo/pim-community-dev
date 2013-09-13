<?php

namespace Oro\Bundle\UserBundle\Tests\Unit\ImportExport\Serializer\Normalizer;

use Oro\Bundle\UserBundle\ImportExport\Serializer\Normalizer\UserNormalizer;
use Oro\Bundle\UserBundle\Entity\User;

class UserNormalizerTest extends \PHPUnit_Framework_TestCase
{
    const USER_TYPE = 'Oro\Bundle\UserBundle\Entity\User';

    /**
     * @var UserNormalizer
     */
    protected $normalizer;

    protected function setUp()
    {
        $this->normalizer = new UserNormalizer();
    }

    public function testSupportsNormalization()
    {
        $this->assertFalse($this->normalizer->supportsNormalization(array()));
        $this->assertTrue($this->normalizer->supportsNormalization($this->createUser()));
    }

    public function testSupportsDenormalization()
    {
        $this->assertFalse($this->normalizer->supportsDenormalization(array(), 'stdClass'));
        $this->assertFalse($this->normalizer->supportsDenormalization('string', self::USER_TYPE));
        $this->assertTrue($this->normalizer->supportsDenormalization(array(), self::USER_TYPE));
    }

    /**
     * @dataProvider normalizeScalarFieldsDataProvider
     */
    public function testNormalizeScalarFields(User $contact, array $expectedData, array $context)
    {
        $this->assertEquals(
            $expectedData,
            $this->normalizer->normalize($contact, null, $context)
        );
    }

    /**
     * @dataProvider normalizeScalarFieldsDataProvider
     */
    public function testDenormalizeScalarFields(User $expectedUser, array $data, array $context)
    {
        $actualUser = $this->normalizer->denormalize($data, self::USER_TYPE, null, $context);
        $actualUser->setSalt($expectedUser->getSalt());
        $this->assertEquals($expectedUser, $actualUser);
    }

    public function normalizeScalarFieldsDataProvider()
    {
        return array(
            'not_empty' => array(
                $this->createUser()
                    ->setFirstName('first_name')
                    ->setLastName('last_name')
                ,
                array(
                    'firstName' => 'first_name',
                    'lastName' => 'last_name',
                ),
                array(
                    'mode' => UserNormalizer::SHORT_MODE
                )
            ),
            'empty' => array(
                $this->createUser(),
                array(
                    'firstName' => null,
                    'lastName' => null,
                ),
                array(
                    'mode' => UserNormalizer::SHORT_MODE
                )
            ),
        );
    }

    /**
     * @return User
     */
    protected function createUser()
    {
        $result = new User();
        $result->setSalt('salt');
        return $result;
    }
}
