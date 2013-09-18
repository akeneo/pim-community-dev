<?php

namespace Oro\Bundle\ImapBundle\Tests\Unit\Form\Type;

use Symfony\Component\Form\Test\FormIntegrationTestCase;

use Oro\Bundle\ImapBundle\Entity\ImapEmailOrigin;
use Oro\Bundle\ImapBundle\Form\Type\ConfigurationType;
use Oro\Bundle\PlatformBundle\Security\Encryptor\Mcrypt;

class ConfigurationTypeTest extends FormIntegrationTestCase
{
    const TEST_PASSWORD = 'somePassword';

    /** @var Mcrypt */
    protected $encryptor;

    public function setUp()
    {
        $this->encryptor = new Mcrypt('someKey');
        parent::setUp();
    }

    public function tearDown()
    {
        parent::tearDown();
        unset($this->encryptor);
    }

    /**
     * @param array      $formData
     * @param array|bool $expectedViewData
     *
     * @param array      $expectedModelData
     *
     * @dataProvider setDataProvider
     */
    public function testBindValidData($formData, $expectedViewData, $expectedModelData)
    {
        $type = new ConfigurationType($this->encryptor);
        $form = $this->factory->create($type);
        if ($expectedViewData) {
            $entity = new ImapEmailOrigin();
            $form->setData($entity);

            $form->submit($formData);
            foreach ($expectedViewData as $name => $value) {
                $this->assertEquals($value, $form->get($name)->getData());
            }

            foreach ($expectedModelData as $name => $value) {
                $this->assertAttributeEquals($value, $name, $entity);
            }
        } else {
            $form->submit($formData);
            $this->assertNull($form->getData());
        }
    }

    /**
     * @return array
     */
    public function setDataProvider()
    {
        $this->setUp();
        $encodedPass = $this->encryptor->encryptData(self::TEST_PASSWORD);

        return array(
            'should bind correct data except password' => array(
                array(
                    'host'     => 'someHost',
                    'port'     => '123',
                    'ssl'      => 'ssl',
                    'user'     => 'someUser',
                    'password' => self::TEST_PASSWORD
                ),
                array(
                    'host' => 'someHost',
                    'port' => '123',
                    'ssl'  => 'ssl',
                    'user' => 'someUser',
                ),
                array(
                    'host'     => 'someHost',
                    'port'     => '123',
                    'ssl'      => 'ssl',
                    'user'     => 'someUser',
                    'password' => $encodedPass
                ),
            ),
            'should not create empty entity'           => array(
                array(
                    'host'     => '',
                    'port'     => '',
                    'ssl'      => '',
                    'user'     => '',
                    'password' => ''
                ),
                false
            )
        );
    }
}
