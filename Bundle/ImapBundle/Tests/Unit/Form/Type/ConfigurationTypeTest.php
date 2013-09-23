<?php

namespace Oro\Bundle\ImapBundle\Tests\Unit\Form\Type;

use Symfony\Component\Form\Test\FormIntegrationTestCase;

use Oro\Bundle\ImapBundle\Entity\ImapEmailOrigin;
use Oro\Bundle\ImapBundle\Form\Type\ConfigurationType;
use Oro\Bundle\SecurityBundle\Encoder\Mcrypt;

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
            $form->submit($formData);
            foreach ($expectedViewData as $name => $value) {
                $this->assertEquals($value, $form->get($name)->getData());
            }

            $entity = $form->getData();
            foreach ($expectedModelData as $name => $value) {
                if ($name == 'password') {
                    $encodedPass = $this->readAttribute($entity, $name);
                    $this->assertEquals($this->encryptor->decryptData($encodedPass), $value);
                } else {
                    $this->assertAttributeEquals($value, $name, $entity);
                }
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
                    'password' => self::TEST_PASSWORD
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
                false,
                false
            )
        );
    }

    /**
     * If submitted empty password, it should be populated from old entity
     */
    public function testBindEmptyPassword()
    {
        $type = new ConfigurationType($this->encryptor);
        $form = $this->factory->create($type);

        $entity = new ImapEmailOrigin();
        $entity->setPassword(self::TEST_PASSWORD);

        $form->setData($entity);
        $form->submit(
            array(
                'host'     => 'someHost',
                'port'     => '123',
                'ssl'      => 'ssl',
                'user'     => 'someUser',
                'password' => ''
            )
        );

        $this->assertEquals(self::TEST_PASSWORD, $entity->getPassword());
    }

    /**
     * In case when user or host field was changed new configuration should be created
     * and old one will be not active
     */
    public function testCreatingNewConfiguration()
    {
        $type = new ConfigurationType($this->encryptor);
        $form = $this->factory->create($type);

        $entity = new ImapEmailOrigin();
        $this->assertTrue($entity->getIsActive());

        $form->setData($entity);
        $form->submit(
            array(
                'host'     => 'someHost',
                'port'     => '123',
                'ssl'      => 'ssl',
                'user'     => 'someUser',
                'password' => 'somPassword'
            )
        );

        $this->assertNotSame($entity, $form->getData());
        $this->assertFalse($entity->getIsActive());

        $this->assertInstanceOf('Oro\Bundle\ImapBundle\Entity\ImapEmailOrigin', $form->getData());
        $this->assertTrue($form->getData()->getIsActive());
    }

    /**
     * Case when user submit empty form but have configuration
     * configuration should be not active and relation should be broken
     */
    public function testSubmitEmptyForm()
    {
        $type = new ConfigurationType($this->encryptor);
        $form = $this->factory->create($type);

        $entity = new ImapEmailOrigin();
        $this->assertTrue($entity->getIsActive());

        $form->setData($entity);
        $form->submit(
            array(
                'host'     => '',
                'port'     => '',
                'ssl'      => '',
                'user'     => '',
                'password' => ''
            )
        );

        $this->assertNotSame($entity, $form->getData());
        $this->assertFalse($entity->getIsActive());

        $this->assertNotInstanceOf('Oro\Bundle\ImapBundle\Entity\ImapEmailOrigin', $form->getData());
        $this->assertNull($form->getData());
    }
}
