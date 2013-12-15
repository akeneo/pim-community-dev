<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Form\Type;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Form\Extension\Validator\Type\FormTypeValidatorExtension;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\Tests\Extension\Core\Type\TypeTestCase;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Driver\SimplifiedYamlDriver;
use Pim\Bundle\TranslationBundle\Form\Type\TranslatableFieldType;
use Pim\Bundle\UIBundle\Form\Type\SwitchType;
use Pim\Bundle\UIBundle\Form\Extension\FormTypeSelect2Extension;

/**
 * Abstract form type test
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractFormTypeTest extends TypeTestCase
{
    /**
     * Create an entityManager for testing
     *
     * @return EntityManager
     */
    public function createTestEntityManager()
    {
        $config = new \Doctrine\ORM\Configuration();
        $config->setEntityNamespaces(array('SymfonyTestsDoctrine' => 'Symfony\Bridge\Doctrine\Tests\Fixtures'));
        $config->setAutoGenerateProxyClasses(true);
        $config->setProxyDir(\sys_get_temp_dir());
        $config->setProxyNamespace('SymfonyTests\Doctrine');
        $bundlePath = __DIR__."/../../../..";
        $yamlDriver = new SimplifiedYamlDriver(
            array(
                $bundlePath."/Resources/config/doctrine" => "Pim\\Bundle\\CatalogBundle\\Entity"
            )
        );

        $config->setMetadataDriverImpl($yamlDriver);
        $config->setQueryCacheImpl(new \Doctrine\Common\Cache\ArrayCache());
        $config->setMetadataCacheImpl(new \Doctrine\Common\Cache\ArrayCache());

        $params = array(
            'driver' => 'pdo_sqlite',
            'memory' => true,
        );

        return EntityManager::create($params, $config);
    }

    /**
     * Create entity form type
     *
     * @return \Symfony\Bridge\Doctrine\Form\Type\EntityType
     */
    protected function createEntityType()
    {
        $em = $this->createTestEntityManager();

        $registry = $this->getMockForAbstractClass('Doctrine\Common\Persistence\ManagerRegistry');
        $registry->expects($this->any())
                 ->method('getManagerForClass')
                 ->will($this->returnValue($em));

        $entityType = new EntityType($registry);

        return $entityType;
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        // redefine form factory and builder to add translatable field
        $this->builder->add('pim_translatable_field');
        $this->builder->add('entity');
        $this->builder->add('switch');

        $this->factory = Forms::createFormFactoryBuilder()
            ->addTypeExtension(new FormTypeSelect2Extension())
            ->addTypeExtension(
                new FormTypeValidatorExtension(
                    $this->getMock('Symfony\Component\Validator\ValidatorInterface')
                )
            )
            ->addType(
                new TranslatableFieldType(
                    $this->getMock('Symfony\Component\Validator\ValidatorInterface'),
                    $this->getLocaleManagerMock(),
                    $this->getLocaleHelperMock()
                )
            )
            ->addType($this->createEntityType())
            ->addType(new SwitchType())
            ->getFormFactory();
    }

    /**
     * Create mock for locale manager
     *
     * @return \Pim\Bundle\CatalogBundle\Manager\LocaleManager
     */
    protected function getLocaleManagerMock()
    {
        $objectManager = $this->getMockForAbstractClass('\Doctrine\Common\Persistence\ObjectManager');
        $securityContext = $this->getSecurityContextMock();
        $securityFacade = $this->getSecurityFacadeMock();

        // create mock builder for locale manager and redefine constructor to set object manager
        $mockBuilder = $this->getMockBuilder('Pim\Bundle\CatalogBundle\Manager\LocaleManager')
            ->setConstructorArgs(array($objectManager, $securityContext, $securityFacade, 'en_US'));

        // create locale manager mock from mock builder previously create and redefine getActiveCodes method
        $localeManager = $mockBuilder->getMock(
            'Pim\Bundle\CatalogBundle\Manager\LocaleManager',
            array('getActiveCodes')
        );
        $localeManager->expects($this->any())
            ->method('getActiveCodes')
            ->will($this->returnValue(array('en_US', 'fr_FR')));

        return $localeManager;
    }

    /**
     * Create a security context mock
     *
     * @return \Symfony\Component\Security\Core\SecurityContext
     */
    protected function getSecurityContextMock()
    {
        $authManager = $this->getMock('Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface');
        $decisionManager = $this->getMock(
            'Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface'
        );

        $securityContext = new SecurityContext($authManager, $decisionManager);
        $securityContext->setToken(
            $this->getMock('Symfony\Component\Security\Core\Authentication\Token\TokenInterface')
        );

        return $securityContext;
    }

    /**
     * Get a mock of ObjectManager
     *
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    protected function getObjectManagerMock()
    {
        return $this->getMock('Doctrine\Common\Persistence\ObjectManager');
    }

    /**
     * Get a mock of EventDispatcherInterface
     *
     * @return \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected function getEventDispatcherInterfaceMock()
    {
        return $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
    }

    /**
     * Get a mock of MediaManager
     *
     * @return \Pim\Bundle\CatalogBundle\Manager\MediaManager
     */
    protected function getMediaManagerMock()
    {
        return $this
            ->getMockBuilder('Pim\Bundle\CatalogBundle\Manager\MediaManager')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Assert field name and type
     * @param string $name Field name
     * @param string $type Field type alias
     */
    protected function assertField($name, $type)
    {
        $formType = $this->form->get($name);
        $this->assertInstanceOf('\Symfony\Component\Form\Form', $formType);
        $this->assertEquals($type, $formType->getConfig()->getType()->getInnerType()->getName());
    }

    /**
     * Get LocaleHelperMock
     *
     * @return \Pim\Bundle\CatalogBundle\Helper\LocaleHelper
     */
    protected function getLocaleHelperMock()
    {
        $helper = $this->getMockBuilder('Pim\Bundle\CatalogBundle\Helper\LocaleHelper')
            ->disableOriginalConstructor()
            ->getMock();
        $helper->expects($this->any())
            ->method('getLocaleLabel')
            ->will($this->returnArgument(0));

        return $helper;
    }

    /**
     * Get ACL SecurityFacade mock
     *
     * @return \Oro\Bundle\SecurityBundle\SecurityFacade
     */
    protected function getSecurityFacadeMock()
    {
        return $this
            ->getMockBuilder('Oro\Bundle\SecurityBundle\SecurityFacade')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
