<?php

namespace Pim\Bundle\CatalogBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Pim\Bundle\CatalogBundle\Entity\ProductAttribute;

/**
 * Product attribute controller
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductAttributeController extends Controller
{
    /**
     * List product attributes
     * @param Request $request
     *
     * @return template
     */
    public function indexAction(Request $request)
    {
        /** @var $gridManager AttributeDatagridManager */
        $gridManager  = $this->get('pim_catalog.datagrid.manager.productattribute');
        $datagrid     = $gridManager->getDatagrid();

        if ('json' == $request->getRequestFormat()) {
            $view = 'OroGridBundle:Datagrid:list.json.php';
        } else {
            $view = 'PimCatalogBundle:ProductAttribute:index.html.twig';
        }

        return $this->render($view, array('datagrid' => $datagrid->createView()));
    }

    /**
     * Create attribute
     *
     * @Template("PimCatalogBundle:ProductAttribute:form.html.twig")
     * @return array
     */
    public function createAction()
    {
        $attribute = $this->getProductManager()->createAttribute('pim_catalog_text');

        if ($this->get('pim_catalog.form.handler.attribute')->process($attribute)) {
            $this->addFlash('success', 'Attribute successfully created');

            return $this->redirectToRoute('pim_catalog_productattribute_edit', array('id' => $attribute->getId()));
        }

        $localeManager = $this->get('pim_catalog.manager.locale');

        return array(
            'form'            => $this->get('pim_catalog.form.attribute')->createView(),
            'locales'         => $localeManager->getActiveLocales(),
            'disabledLocales' => $localeManager->getDisabledLocales(),
            'measures'        => $this->container->getParameter('oro_measure.measures_config')
        );
    }

    /**
     * Edit attribute form
     *
     * @param Request          $request
     * @param ProductAttribute $attribute
     *
     * @Template("PimCatalogBundle:ProductAttribute:form.html.twig")
     * @return array
     */
    public function editAction(Request $request, ProductAttribute $attribute)
    {
        if ($this->get('pim_catalog.form.handler.attribute')->process($attribute)) {
            $this->addFlash('success', 'Attribute successfully saved');

            return $this->redirectToRoute('pim_catalog_productattribute_edit', array('id' => $attribute->getId()));
        }

        $localeManager = $this->get('pim_catalog.manager.locale');
        $datagrid = $this->getDataAuditDatagrid(
            $attribute,
            'pim_catalog_productattribute_edit',
            array('id' => $attribute->getId())
        );
        $datagridView = $datagrid->createView();

        if ('json' == $request->getRequestFormat()) {
            return $this->get('oro_grid.renderer')->renderResultsJsonResponse($datagridView);
        }

        $auditManager = $this->container->get('pim_versioning.manager.audit');

        return array(
            'form'            => $this->get('pim_catalog.form.attribute')->createView(),
            'locales'         => $localeManager->getActiveLocales(),
            'disabledLocales' => $localeManager->getDisabledLocales(),
            'measures'        => $this->container->getParameter('oro_measure.measures_config'),
            'datagrid'        => $datagridView,
            'created'         => $auditManager->getFirstLogEntry($attribute),
            'updated'         => $auditManager->getLastLogEntry($attribute),
        );
    }

    /**
     * Preprocess attribute form
     *
     * @param Request $request
     *
     * @Template("PimCatalogBundle:ProductAttribute:_form_parameters.html.twig")
     * @return array
     */
    public function preProcessAction(Request $request)
    {
        $data = $request->request->all();
        if (!isset($data['pim_catalog_attribute_form'])) {
            return $this->redirectToRoute('pim_catalog_productattribute_create');
        }

        // Add custom fields to the form and set the entered data to the form
        $this
            ->get('pim_catalog.form.handler.attribute')
            ->preProcess($data['pim_catalog_attribute_form']);

        $localeManager   = $this->get('pim_catalog.manager.locale');
        $locales         = $localeManager->getActiveLocales();
        $disabledLocales = $localeManager->getDisabledLocales();
        $form            = $this->get('pim_catalog.form.attribute')->createView();

        $data = array(
            'parameters' => $this->renderView(
                'PimCatalogBundle:ProductAttribute:_form_parameters.html.twig',
                array(
                    'form'            => $form,
                    'locales'         => $locales,
                    'disabledLocales' => $disabledLocales
                )
            ),
            'values' => $this->renderView(
                'PimCatalogBundle:ProductAttribute:_form_values.html.twig',
                array(
                    'form'            => $form,
                    'locales'         => $locales,
                    'disabledLocales' => $disabledLocales
                )
            )
        );

        return new JsonResponse($data);
    }

    /**
     * Edit ProductAttribute sort order
     *
     * @param Request $request
     *
     * @return Response
     */
    public function sortAction(Request $request)
    {
        if (!$request->isXmlHttpRequest()) {
            return $this->redirectToRoute('pim_catalog_productattribute_index');
        }

        $data = $request->request->all();

        if (!empty($data)) {
            foreach ($data as $id => $sort) {
                $attribute = $this->getRepository('PimCatalogBundle:ProductAttribute')->find((int) $id);
                if ($attribute) {
                    $attribute->setSortOrder((int) $sort);
                    $this->persist($attribute, false);
                }
            }
            $this->flush();

            return new Response(1);
        }

        return new Response(0);
    }

    /**
     * Remove attribute
     *
     * @param Request          $request
     * @param ProductAttribute $entity
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function removeAction(Request $request, ProductAttribute $entity)
    {
        if ($entity->getAttributeType() === 'pim_catalog_identifier') {
            if ($request->isXmlHttpRequest()) {
                return new Response('', 403);
            } else {
                return $this->redirectToRoute('pim_catalog_productattribute_index');
            }
        }

        $this->remove($entity);

        if ($request->isXmlHttpRequest()) {
            return new Response('', 204);
        } else {
            return $this->redirectToRoute('pim_catalog_productattribute_index');
        }
    }
}
