<?php

namespace Pim\Bundle\CustomEntityBundle\Tests\Unit\Controller\Strategy;

use Pim\Bundle\CustomEntityBundle\Controller\Strategy\CrudStrategy;

/**
 * Tests related class
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CrudStrategyTest extends AbstractStrategyTest
{
    protected $strategy;

    protected function setUp()
    {
        parent::setUp();
        $this->strategy = new CrudStrategy($this->formFactory, $this->templating, $this->router, $this->translator);
    }

    public function getFormActionData()
    {
        return array(
            'display'   => array(false, false),
            'error'     => array(true, false),
            'valid'     => array(true, true)
        );
    }
    /**
     * @dataProvider getFormActionData
     */
    public function testCreateAction($post, $valid)
    {
        $entity = new \stdClass;
        $this->configuration->expects($this->any())
            ->method('getCreateDefaultProperties')
            ->will($this->returnValue(array('create_default_properties')));
        $this->configuration->expects($this->any())
            ->method('getCreateOptions')
            ->will($this->returnValue(array('create_options')));
        $this->configuration->expects($this->any())
            ->method('getCreateTemplate')
            ->will($this->returnValue('create_template'));
        $this->configuration->expects($this->any())
            ->method('getCreateFormType')
            ->will($this->returnValue('create_form_type'));
        $this->configuration->expects($this->any())
            ->method('getCreateFormOptions')
            ->will($this->returnValue(array('create_form_options')));
        $this->configuration->expects($this->any())
            ->method('getCreateRedirectRoute')
            ->will($this->returnValue('create_redirect_route'));
        $this->configuration->expects($this->any())
            ->method('getCreateRedirectRouteParameters')
            ->will($this->returnValue(array('create_redirect_route_parameters')));

        $this->manager->expects($this->once())
            ->method('create')
            ->with(
                $this->equalTo('entity_class'),
                $this->equalTo(array('create_default_properties')),
                $this->equalTo(array('create_options'))
            )
            ->will($this->returnValue($entity));

        $this->request->expects($this->once())
            ->method('getMethod')
            ->will($this->returnValue($post ? 'POST' : 'GET'));

        $form = $this->getMockForm('create_form_type', $entity, array('create_form_options'));
        if ($post) {
            $form->expects($this->once())
                ->method('bind')
                ->with($this->identicalTo($this->request));
            $form->expects($this->once())
                ->method('isValid')
                ->will($this->returnValue($valid));
        } else {
            $form->expects($this->never())
                ->method('bind');
        }

        if ($valid) {
            $this->router->expects($this->once())
                ->method('generate')
                ->with(
                    $this->equalTo('create_redirect_route'),
                    $this->equalTo(array('create_redirect_route_parameters'))
                );
            $this->assertFlash('success', 'flash.name.created');
        } else {
            $form->expects($this->once())
                ->method('createView')
                ->will($this->returnValue('form_view'));
            $this->assertRendered('create_template', array('form'=>'form_view'));
        }

        $result = $this->strategy->createAction($this->configuration, $this->request);

        if ($valid) {
            $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $result);
        } else {
            $this->assertEquals('success', $result);
        }
    }

    /**
     * @dataProvider getFormActionData
     */
    public function testEditAction($post, $valid)
    {
        $entity = new \stdClass;
        $this->request->attributes->expects($this->once())
            ->method('get')
            ->with($this->equalTo(('id')))
            ->will($this->returnValue('id'));
        $this->configuration->expects($this->any())
            ->method('getFindOptions')
            ->will($this->returnValue(array('find_options')));
        $this->configuration->expects($this->any())
            ->method('getEditTemplate')
            ->will($this->returnValue('edit_template'));
        $this->configuration->expects($this->any())
            ->method('getEditFormType')
            ->will($this->returnValue('edit_form_type'));
        $this->configuration->expects($this->any())
            ->method('getEditFormOptions')
            ->will($this->returnValue(array('edit_form_options')));
        $this->configuration->expects($this->any())
            ->method('getEditRedirectRoute')
            ->will($this->returnValue('edit_redirect_route'));
        $this->configuration->expects($this->any())
            ->method('getEditRedirectRouteParameters')
            ->will($this->returnValue(array('edit_redirect_route_parameters')));
        $this->manager->expects($this->once())
            ->method('find')
            ->with(
                $this->equalTo('entity_class'),
                $this->equalTo('id'),
                $this->equalTo(array('find_options'))
            )
            ->will($this->returnValue($entity));
        $form = $this->getMockForm('edit_form_type', $entity, array('edit_form_options'));
        $this->request->expects($this->once())
            ->method('getMethod')
            ->will($this->returnValue($post ? 'POST' : 'GET'));
        if ($post) {
            $form->expects($this->once())
                ->method('bind')
                ->with($this->identicalTo($this->request));
            $form->expects($this->once())
                ->method('isValid')
                ->will($this->returnValue($valid));
        } else {
            $form->expects($this->never())
                ->method('bind');
        }
        if ($valid) {
            $this->router->expects($this->once())
                ->method('generate')
                ->with(
                    $this->equalTo('edit_redirect_route'),
                    $this->equalTo(array('edit_redirect_route_parameters'))
                )
                ->will($this->returnValue('url'));
            $this->assertFlash('success', 'flash.name.updated');
        } else {
            $form->expects($this->once())
                ->method('createView')
                ->will($this->returnValue('form_view'));
            $this->assertRendered('edit_template', array('form'=>'form_view'));
        }
        $result = $this->strategy->editAction($this->configuration, $this->request);
        if ($valid) {
            $this->assertInstanceOf('Symfony\Component\HttpFoundation\RedirectResponse', $result);
            $this->assertEquals('url', $result->getTargetUrl());
        } else {
            $this->assertEquals('success', $result);
        }
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function testEditNotFound()
    {
        $this->configuration->expects($this->any())
            ->method('getFindOptions')
            ->will($this->returnValue(array('find_options')));
        $this->manager->expects($this->once())
            ->method('find')
            ->will($this->returnValue(null));
        $this->strategy->editAction($this->configuration, $this->request);
    }

    public function testRemove()
    {
        $entity = new \stdClass;
        $this->request->attributes->expects($this->once())
            ->method('get')
            ->with($this->equalTo(('id')))
            ->will($this->returnValue('id'));
        $this->configuration->expects($this->any())
            ->method('getFindOptions')
            ->will($this->returnValue(array('find_options')));
        $this->manager->expects($this->once())
            ->method('find')
            ->with(
                $this->equalTo('entity_class'),
                $this->equalTo('id'),
                $this->equalTo(array('find_options'))
            )
            ->will($this->returnValue($entity));
        $this->manager->expects($this->once())
            ->method('remove')
            ->with($this->equalTo($entity));
        $response = $this->strategy->removeAction($this->configuration, $this->request);
        $this->assertInstanceOf('Symfony\Component\HttpFoundation\Response', $response);
        $this->assertEquals(204, $response->getStatusCode());
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function testRemoveNotFound()
    {
        $this->configuration->expects($this->any())
            ->method('getFindOptions')
            ->will($this->returnValue(array('find_options')));
        $this->manager->expects($this->once())
            ->method('find')
            ->will($this->returnValue(null));
        $this->strategy->removeAction($this->configuration, $this->request);
    }

    protected function getMockForm($type, $entity, $options)
    {
        $form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();
        $this->formFactory->expects($this->once())
            ->method('create')
            ->with($this->equalTo($type), $this->identicalTo($entity), $this->equalTo($options))
            ->will($this->returnValue($form));

        return $form;
    }

    protected function assertFlash($type, $message)
    {
        $this->translator
            ->expects($this->once())
            ->method('trans')
            ->with($this->equalTo($message))
            ->will($this->returnValue('translated'));

        $flashBag = $this->getMock('Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface');
        $this->session
            ->expects($this->once())
            ->method('getFlashBag')
            ->will($this->returnValue($flashBag));

        $flashBag
            ->expects($this->once())
            ->method('add')
            ->with($this->equalTo($type), $this->equalTo('translated'));
    }
}
