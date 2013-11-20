<?php

namespace Pim\Bundle\CustomEntityBundle\Tests\Unit\Configuration;

use Symfony\Component\PropertyAccess\PropertyAccessor;
use Pim\Bundle\CustomEntityBundle\Configuration\Configuration;

/**
 * Tests related class
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    protected $propertyAccessor;
    protected $manager;
    protected $worker;

    protected function setUp()
    {
        $this->propertyAccessor = new PropertyAccessor;
        $this->manager = $this->getMock('Pim\Bundle\CustomEntityBundle\Manager\ManagerInterface');
        $this->worker = $this->getMock('Pim\Bundle\CustomEntityBundle\ControllerWorker\WorkerInterface');
    }

    public function testDefaultOptions()
    {
        $this->assertConfigValues(
            $this->getConfiguration(),
            array(
                'name'                              => 'name',
                'entity_class'                      => 'entity_class',
                'edit_form_type'                    => 'edit_form_type',
                'base_template'                     => 'PimCustomEntityBundle::layout.html.twig',
                'edit_template'                     => 'PimCustomEntityBundle:CustomEntity:edit.html.twig',
                'index_template'                    => 'PimCustomEntityBundle:CustomEntity:index.html.twig',
                'create_template'                   => 'PimCustomEntityBundle:CustomEntity:quickcreate.html.twig',
                'create_form_options'               => array(),
                'create_form_type'                  => 'edit_form_type',
                'create_default_properties'         => array(),
                'create_options'                    => array(),
                'index_route'                       => 'pim_customentity_index',
                'create_route'                      => 'pim_customentity_create',
                'edit_route'                        => 'pim_customentity_edit',
                'remove_route'                      => 'pim_customentity_remove',
                'edit_form_options'                 => array(),
                'find_options'                      => array(),
                'query_builder_options'             => array(),
                'datagrid_namespace'                => 'pim_custom_entity',
                'manager'                           => $this->manager,
                'worker'                            => $this->worker
            )
        );
    }

    public function testUserOptions()
    {
        $userOptions = array(
            'entity_class'                      => 'entity_class',
            'edit_form_type'                    => 'edit_form_type',
            'base_template'                     => 'base_template',
            'edit_template'                     => 'edit_template',
            'index_template'                    => 'index_template',
            'create_template'                   => 'create_template',
            'create_form_options'               => 'create_form_options',
            'create_form_type'                  => 'create_form_type',
            'create_default_properties'         => 'create_default_properties',
            'create_options'                    => 'create_options',
            'index_route'                       => 'index_route',
            'create_route'                      => 'create_route',
            'edit_route'                        => 'edit_route',
            'remove_route'                      => 'remove_route',
            'edit_form_options'                 => 'edit_form_options',
            'find_options'                      => 'find_options',
            'query_builder_options'             => 'query_builder_options',
            'datagrid_namespace'                => 'datagrid_namespace'
        );
        $this->assertConfigValues($this->getConfiguration($userOptions), $userOptions);
    }

    public function testWithoutCreateForm()
    {
        $this->assertConfigValues(
            $this->getConfiguration(
                array(
                    'edit_form_options' => 'edit_form_options'
                )
            ),
            array(
                'edit_form_type'        => 'edit_form_type',
                'create_form_type'      => 'edit_form_type',
                'edit_form_options'     => 'edit_form_options',
                'create_form_options'   => 'edit_form_options',
            )
        );
    }

    public function testGetCreateRedirectAsIndex()
    {
        $entity = $this->getMockBuilder('stdClass')
            ->getMock();

        $configuration = $this->getConfiguration(
            array(
                'index_route'           =>'index_route',
                'edit_after_create'     => false
            )
        );
        $this->assertEquals('index_route', $configuration->getCreateRedirectRoute($entity));
        $this->assertEquals(
            array('customEntityName' => 'name'),
            $configuration->getCreateRedirectRouteParameters($entity)
        );
    }

    public function testGetCreateRedirectAsEdit()
    {
        $entity = $this->getMockBuilder('stdClass')
            ->setMethods(array('getId'))
            ->getMock();

        $entity->expects($this->any())
            ->method('getId')
            ->will($this->returnValue('id'));

        $configuration = $this->getConfiguration(
            array(
                'edit_route'           =>'edit_route',
            )
        );
        $this->assertEquals('edit_route', $configuration->getCreateRedirectRoute($entity));
        $this->assertEquals(
            array('customEntityName' => 'name', 'id' => 'id'),
            $configuration->getCreateRedirectRouteParameters($entity)
        );
    }

    public function testGetEditRedirect()
    {
        $entity = $this->getMockBuilder('stdClass')
            ->getMock();

        $configuration = $this->getConfiguration(
            array(
                'index_route'           =>'index_route',
            )
        );
        $this->assertEquals('index_route', $configuration->getEditRedirectRoute($entity));
        $this->assertEquals(
            array('customEntityName' => 'name'),
            $configuration->getEditRedirectRouteParameters($entity)
        );
    }

    protected function assertConfigValues(Configuration $configuration, array $values)
    {
        foreach ($values as $propertyPath => $value) {
            $this->assertEquals(
                $value,
                $this->propertyAccessor->getValue($configuration, $propertyPath),
                sprintf('Bad value for property %s', $propertyPath)
            );
        }
    }

    protected function getConfiguration(array $options = array())
    {
        $options = $options + array(
            'entity_class'      => 'entity_class',
            'edit_form_type'    => 'edit_form_type'
        );

        return new Configuration('name', $this->manager, $this->worker, $options);
    }
}
