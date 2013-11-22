<?php

namespace Pim\Bundle\CustomEntityBundle\Tests\Unit\Controller\Strategy;

/**
 * Base class for worker tests
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractStrategyTest extends \PHPUnit_Framework_TestCase
{
    protected $formFactory;
    protected $templating;
    protected $router;
    protected $translator;
    protected $request;
    protected $session;
    protected $configuration;
    protected $manager;

    protected function setUp()
    {
        $this->formFactory = $this->getMock('Symfony\Component\Form\FormFactoryInterface');

        $this->templating = $this->getMock('Symfony\Bundle\FrameworkBundle\Templating\EngineInterface');

        $this->router = $this->getMock('Symfony\Component\Routing\RouterInterface');

        $this->session = $this->getMockBuilder('Symfony\Component\HttpFoundation\Session\Session')
            ->disableOriginalConstructor()
            ->getMock();

        $this->translator = $this->getMock('Symfony\Component\Translation\TranslatorInterface');

        $this->request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->getMock();
        $this->request->expects($this->any())
            ->method('getSession')
            ->will($this->returnValue($this->session));
        $this->request->attributes = $this->getMockBuilder('Symfony\Component\HttpFoundation\ParameterBag')
            ->disableOriginalConstructor()
            ->getMock();

        $this->manager = $this->getMock('Pim\Bundle\CustomEntityBundle\Manager\OrmManagerInterface');

        $this->configuration = $this->getMock('Pim\Bundle\CustomEntityBundle\Configuration\ConfigurationInterface');
        $this->configuration->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('name'));
        $this->configuration->expects($this->any())
            ->method('getEntityClass')
            ->will($this->returnValue('entity_class'));
        $this->configuration->expects($this->any())
            ->method('getBaseTemplate')
            ->will($this->returnValue('base_template'));
        $this->configuration->expects($this->any())
            ->method('getIndexRoute')
            ->will($this->returnValue('index_route'));
        $this->configuration->expects($this->any())
            ->method('getEditRoute')
            ->will($this->returnValue('edit_route'));
        $this->configuration->expects($this->any())
            ->method('getCreateRoute')
            ->will($this->returnValue('create_route'));
        $this->configuration->expects($this->any())
            ->method('getRemoveRoute')
            ->will($this->returnValue('remove_route'));
        $this->configuration->expects($this->any())
            ->method('getEntityClass')
            ->will($this->returnValue('entity_class'));
        $this->configuration->expects($this->any())
            ->method('getManager')
            ->will($this->returnValue($this->manager));
    }

    protected function assertFlash($type, $message)
    {
        $this->translator
            ->expects($this->once())
            ->method('trans')
            ->with($this->equalTo($message))
            ->will($this->returnValue('translated'));

        $this->session
            ->expects($this->once())
            ->method('addFlash')
            ->with($this->equalTo($type), $this->equalTo('translated'));
    }

    protected function assertRendered($template, array $parameters = array())
    {
        $this->templating->expects($this->once())
            ->method('renderResponse')
            ->with(
                $this->equalTo($template),
                $this->equalTo(
                    array(
                        'customEntityName' => 'name',
                        'baseTemplate'     => 'base_template',
                        'indexRoute'       => 'index_route',
                        'editRoute'        => 'edit_route',
                        'createRoute'      => 'create_route',
                        'removeRoute'      => 'remove_route'
                    ) + $parameters
                )
            )
            ->will($this->returnValue('success'));
    }
}
