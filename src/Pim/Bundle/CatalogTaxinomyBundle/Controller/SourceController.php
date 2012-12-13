<?php
namespace Pim\Bundle\CatalogTaxinomyBundle\Controller;

use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Pim\Bundle\CatalogTaxinomyBundle\Form\Type\SourceType;
use Pim\Bundle\CatalogTaxinomyBundle\Entity\Source;

use APY\DataGridBundle\Grid\Source\Entity as GridEntity;
use APY\DataGridBundle\Grid\Action\RowAction;

/**
 * Source controller
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Route("/source")
 */
class SourceController extends Controller
{
    /**
     * Get object name and repository name
     * @return string
     */
    protected function getObjectShortName()
    {
        return 'PimCatalogTaxinomyBundle:Source';
    }

    /**
     * Get used object manager
     * @return string
     */
    protected function getObjectManagerService()
    {
        return 'doctrine.orm.entity_manager';
    }

    /**
     * Return full name of object class
     * @return string
     */
    protected function getObjectClassFullName()
    {
        $objectManager = $this->get($this->getObjectManagerService());
        $metadata = $objectManager->getClassMetadata($this->getObjectShortName());
        $classFullName = $metadata->getName();

        return $classFullName;
    }

    /**
     * Return new instance of object
     * @return Source
     */
    protected function getNewObject()
    {
        $classFullName = $this->getObjectClassFullName();
        $entity = new $classFullName();

        return $entity;
    }

    /**
     * Lists all sources
     *
     * @Route("/index")
     * @Template()
     *
     * @return Response
     */
    public function indexAction()
    {
        $source = new GridEntity($this->getObjectShortName());
        $grid = $this->get('grid');
        $grid->setSource($source);

        // add an action columns
        $grid->setActionsColumnSeparator('&nbsp;');
        $rowAction = new RowAction('bap.action.edit', 'pim_catalogtaxinomy_source_edit', false, '_self', array('class' => 'grid_action ui-icon-fugue-pencil'));
        $rowAction->setRouteParameters(array('id'));
        $grid->addRowAction($rowAction);

        $rowAction = new RowAction('bap.action.delete', 'pim_catalogtaxinomy_source_delete', true, '_self', array('class' => 'grid_action ui-icon-fugue-minus'));
        $rowAction->setRouteParameters(array('id'));
        $grid->addRowAction($rowAction);

        return $grid->getGridResponse('PimCatalogTaxinomyBundle:Source:index.html.twig');
    }

    /**
     * Displays a form to create a new source
     *
     * @Route("/new")
     * @Template()
     *
     * @return Response
     */
    public function newAction()
    {
        $entity = $this->getNewObject();
        $form = $this->createForm(new SourceType(), $entity);
        $formAction = $this->generateUrl('pim_catalogtaxinomy_source_create');
        // render form
        return $this->render(
            'PimCatalogTaxinomyBundle:Source:edit.html.twig',
            array('entity' => $entity, 'form' => $form->createView(), 'formAction' => $formAction)
        );
    }

    /**
     * Creates a new source
     *
     * @param Request $request
     *
     * @Route("/create")
     * @Method("POST")
     *
     * @return Response
     * @throws \Exception
     */
    public function createAction(Request $request)
    {
        $entity  = $this->getNewObject();
        $form = $this->createForm(new SourceType(), $entity);
        $form->bind($request);

        if ($form->isValid()) {

            try {
                $manager = $this->get($this->getObjectManagerService());
                $manager->persist($entity);
                $manager->flush();

                $this->get('session')->setFlash('success', 'Source has been created');

                return $this->redirect(
                    $this->generateUrl('pim_catalogtaxinomy_source_edit', array('id' => $entity->getId()))
                );

            } catch (\Exception $e) {
                $this->get('session')->setFlash('error', $e->getMessage());
            }

        }

        // render form with error
        $formAction = $this->generateUrl('pim_catalogtaxinomy_source_create');

        return $this->render(
            'PimCatalogTaxinomyBundle:Source:edit.html.twig',
            array('entity' => $entity, 'form' => $form->createView(), 'formAction' => $formAction)
        );
    }

    /**
     * Displays a form to edit an existing source entity.
     *
     * @param integer $id
     *
     * @Route("/{id}/edit")
     * @Template()
     *
     * @return Response
     * @throws NotFoundHttpException
     */
    public function editAction($id)
    {
        $manager = $this->get($this->getObjectManagerService());

        $entity = $manager->getRepository($this->getObjectShortName())->find($id);

        if (!$entity) {
            // TODO : must be another exception. This exception is only for Http request
            throw $this->createNotFoundException('Unable to find source.');
        }

        $form = $this->createForm(new SourceType(), $entity);
        $formAction = $this->generateUrl('pim_catalogtaxinomy_source_update', array('id' => $entity->getId()));

        $params = array('entity' => $entity, 'form' => $form->createView(), 'formAction' => $formAction);

        // render form
        return $this->render('PimCatalogTaxinomyBundle:Source:edit.html.twig', $params);
    }

    /**
     * Edits an existing source entity.
     *
     * @param Request $request request
     * @param integer $id      source id
     *
     * @Route("/{id}/update")
     * @Method("POST")
     *
     * @return Response
     * @throws NotFoundHttpException
     * @throws \Exception
     */
    public function updateAction(Request $request, $id)
    {
        $manager = $this->get($this->getObjectManagerService());

        $entity = $manager->getRepository($this->getObjectShortName())->find($id);

        if (!$entity) {
            // TODO : must be another exception. This exception is only for Http request
            throw $this->createNotFoundException('Unable to find source.');
        }

        $form = $this->createForm(new SourceType(), $entity);
        $form->bind($request);

        if ($form->isValid()) {

            try {
                $manager->persist($entity);
                $manager->flush();
                $this->get('session')->setFlash('success', 'Source has been updated');

                return $this->redirect($this->generateUrl('pim_catalogtaxinomy_source_edit', array('id' => $id)));
            } catch (\Exception $e) {
                $this->get('session')->setFlash('error', $e->getMessage());
            }
        }

        // render form with error
        $formAction = $this->generateUrl('pim_catalogtaxinomy_source_update', array('id' => $entity->getId()));

        return $this->render(
            'PimCatalogTaxinomyBundle:Source:edit.html.twig',
            array('entity' => $entity, 'form' => $form->createView(), 'formAction' => $formAction)
        );
    }

    /**
     * Delete an existing source entity.
     *
     * @param integer $id
     *
     * @Route("/{id}/delete")
     * @Template()
     *
     * @return Response
     * @throws NotFoundHttpException
     */
    public function deleteAction($id)
    {
        $manager = $this->get($this->getObjectManagerService());
        $entity = $manager->getRepository($this->getObjectShortName())->find($id);
        if (!$entity) {
            // TODO : must be another exception. This exception is only for Http request
            throw $this->createNotFoundException('Unable to find source.');
        }
        // delete
        $manager->remove($entity);
        $manager->flush();
        $this->get('session')->setFlash('success', "Source '{$entity->getCode()}' has been deleted");

        return $this->redirect($this->generateUrl('pim_catalogtaxinomy_source_index'));
    }

}
