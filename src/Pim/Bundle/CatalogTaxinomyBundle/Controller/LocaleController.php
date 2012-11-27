<?php
namespace Pim\Bundle\CatalogTaxinomyBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Pim\Bundle\CatalogTaxinomyBundle\Form\Type\LocaleType;
use APY\DataGridBundle\Grid\Source\Entity as GridEntity;
use APY\DataGridBundle\Grid\Action\RowAction;

/**
 * Locale controller.
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Route("/locale")
 */
class LocaleController extends Controller
{

    /**
     * (non-PHPdoc)
     * @see Parent
     * @return string
     */
    public function getObjectShortName()
    {
        return 'PimCatalogTaxinomyBundle:Locale';
    }

    /**
     * Get used object manager
     * @return string
     */
    public function getObjectManagerService()
    {
        return 'doctrine.orm.entity_manager';
    }

    /**
     * Return full name of object class
     * @return unknown
     */
    public function getObjectClassFullName()
    {
        $om = $this->get($this->getObjectManagerService());
        $metadata = $om->getClassMetadata($this->getObjectShortName());
        $classFullName = $metadata->getName();

        return $classFullName;
    }

    /**
     * Return new instance of object
     * @return unknown
     */
    public function getNewObject()
    {
         $classFullName = $this->getObjectClassFullName();
        $entity = new $classFullName();

        return $entity;
    }

    /**
     * Lists all locales
     *
     * @Route("/index")
     * @Template()
     *
     * @return multitype
     */
    public function indexAction()
    {
        $source = new GridEntity($this->getObjectShortName());
        $grid = $this->get('grid');
        $grid->setSource($source);

        // add an action column
        $grid->setActionsColumnSeparator('&nbsp;');
        $rowAction = new RowAction('bap.action.edit', 'pim_catalogtaxinomy_locale_edit', false, '_self', array('class' => 'grid_action ui-icon-fugue-tag--pencil'));
        $rowAction->setRouteParameters(array('id'));
        $grid->addRowAction($rowAction);

/*        $rowAction = new RowAction('bap.action.delete', 'pim_catalogtaxinomy_locale_delete', true, '_self', array('class' => 'grid_action ui-icon-fugue-tag--minus'));
        $rowAction->setRouteParameters(array('id'));
        $grid->addRowAction($rowAction);*/

        return $grid->getGridResponse('PimCatalogTaxinomyBundle:Locale:index.html.twig');
    }


    /**
     * Displays a form to create a new locale
     *
     * @Route("/new")
     * @Template()
     *
     * @return multitype
     */
    public function newAction()
    {
        $entity = $this->getNewObject();
        $form = $this->createForm(new LocaleType(), $entity);
        $formAction = $this->generateUrl('pim_catalogtaxinomy_locale_create');

        // render form
        return $this->render(
            'PimCatalogTaxinomyBundle:Locale:edit.html.twig',
            array('entity' => $entity, 'form' => $form->createView(), 'formAction' => $formAction)
        );
    }

    /**
     * Creates a new locale
     *
     * @param Request $request
     *
     * @Route("/create")
     * @Method("POST")
     *
     * @return multitype
     */
    public function createAction(Request $request)
    {
        $entity  = $this->getNewObject();
        $form = $this->createForm(new LocaleType(), $entity);
        $form->bind($request);

        if ($form->isValid()) {

            $manager = $this->get($this->getObjectManagerService());
            $manager->persist($entity);

            // has default locale
/*            if (!$this->hasDefaultLocale($entity)) {
                return $this->redirect($this->generateUrl('pim_catalogtaxinomy_locale_new'));
            }*/

            $manager->flush();
            $this->get('session')->setFlash('success', 'Locale has been created');

            return $this->redirect($this->generateUrl('pim_catalogtaxinomy_locale_edit', array('id' => $entity->getId())));
        }

        return array('entity' => $entity, 'form' => $form->createView());
    }

    /**
     * Displays a form to edit an existing locale entity.
     *
     * @param integer $id
     *
     * @Route("/{id}/edit")
     * @Template()
     *
     * @return multitype
     */
    public function editAction($id)
    {
        $manager = $this->get($this->getObjectManagerService());

        $entity = $manager->getRepository($this->getObjectShortName())->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find locale.');
        }

        $form = $this->createForm(new LocaleType(), $entity);
        $formAction = $this->generateUrl('pim_catalogtaxinomy_locale_update', array('id' => $entity->getId()));

        $params = array(
            'entity'     => $entity,
            'form'       => $form->createView(),
            'formAction' => $formAction
        );

        // render form
        return $this->render('PimCatalogTaxinomyBundle:Locale:edit.html.twig', $params);
    }

    /**
     * Edits an existing locale entity.
     *
     * @param Request $request request
     * @param integer $id      locale id
     *
     * @Route("/{id}/update")
     * @Method("POST")
     *
     * @return multitype
     */
    public function updateAction(Request $request, $id)
    {
        $manager = $this->get($this->getObjectManagerService());

        $entity = $manager->getRepository($this->getObjectShortName())->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find locale.');
        }

        $classFullName = $this->getObjectClassFullName();
        $form = $this->createForm(new LocaleType(), $entity);
        $form->bind($request);

        if ($form->isValid()) {
            $manager->persist($entity);

            /*
            // change old default locale
            if ($entity->getIsDefault()) {
                $this->disableOldDefaultChannel();
                // check there is a default locale
            } elseif (!$this->hasDefaultChannel()) {
                $this->get('session')->setFlash('error', 'There is no default locale');

                return $this->redirect($this->generateUrl('pim_catalogtaxinomy_locale_edit', array('id' => $id)));
            } elseif (!$this->hasDefaultLocale($entity)) {
                return $this->redirect($this->generateUrl('pim_catalogtaxinomy_locale_edit', array('id' => $id)));
            }
            */

            $manager->flush();
            $this->get('session')->setFlash('success', 'Channel has been updated');

            return $this->redirect($this->generateUrl('pim_catalogtaxinomy_locale_edit', array('id' => $id)));
        }

        return array('entity' => $entity, 'edit_form' => $form->createView());
    }

}
