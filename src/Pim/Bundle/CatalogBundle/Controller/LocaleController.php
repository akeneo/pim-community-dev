<?php

namespace Pim\Bundle\CatalogBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Oro\Bundle\UserBundle\Annotation\Acl;
use Pim\Bundle\CatalogBundle\Entity\Locale;

/**
 * Locale controller for configuration
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Acl(
 *      id="pim_catalog_locale",
 *      name="Locale manipulation",
 *      description="Locale manipulation",
 *      parent="pim_catalog"
 * )
 */
class LocaleController extends Controller
{
    /**
     * List locales
     *
     * @param Request $request
     * @Acl(
     *      id="pim_catalog_locale_index",
     *      name="View locale list",
     *      description="View locale list",
     *      parent="pim_catalog_locale"
     * )
     * @return Response
     */
    public function indexAction(Request $request)
    {
        /** @var $queryBuilder QueryBuilder */
        $queryBuilder = $this->getManager()->createQueryBuilder();
        $queryBuilder
            ->select('l')
            ->from('PimCatalogBundle:Locale', 'l');

        /** @var $queryFactory QueryFactory */
        $queryFactory = $this->get('pim_catalog.datagrid.manager.locale.default_query_factory');
        $queryFactory->setQueryBuilder($queryBuilder);

        /** @var $datagridManager LocaleDatagridManager */
        $datagridManager = $this->get('pim_catalog.datagrid.manager.locale');
        $datagrid = $datagridManager->getDatagrid();

        $view = ('json' === $request->getRequestFormat()) ?
            'OroGridBundle:Datagrid:list.json.php' : 'PimCatalogBundle:Locale:index.html.twig';

        return $this->render($view, array('datagrid' => $datagrid->createView()));
    }

    /**
     * Edit locale
     *
     * @param Locale $locale
     *
     * @Template
     * @Acl(
     *      id="pim_catalog_locale_edit",
     *      name="Edit a locale",
     *      description="Edit a locale",
     *      parent="pim_catalog_locale"
     * )
     * @return array
     */
    public function editAction(Locale $locale)
    {
        if ($this->get('pim_catalog.form.handler.locale')->process($locale)) {
            $this->addFlash('success', 'Locale successfully saved');

            return $this->redirect(
                $this->generateUrl('pim_catalog_locale_index')
            );
        }

        return array(
            'form' => $this->get('pim_catalog.form.locale')->createView()
        );
    }

    /**
     * Disable locale
     *
     * @param Request $request
     * @param Locale  $locale
     * @Acl(
     *      id="pim_catalog_locale_disable",
     *      name="Disable a locale",
     *      description="Disable a locale",
     *      parent="pim_catalog_locale"
     * )
     * @return Response
     */
    public function disableAction(Request $request, Locale $locale)
    {
        $locale->setActivated(false);
        $this->persist($locale);

        if ($request->isXmlHttpRequest()) {
            return new Response('', 204);
        } else {
            return $this->redirect($this->generateUrl('pim_catalog_locale_index'));
        }
    }

    /**
     * Enable locale
     *
     * @param Request $request
     * @param Locale  $locale
     * @Acl(
     *      id="pim_catalog_locale_enable",
     *      name="Enable a locale",
     *      description="Enable a locale",
     *      parent="pim_catalog_locale"
     * )
     * @return Response
     */
    public function enableAction(Request $request, Locale $locale)
    {
        $locale->setActivated(true);
        $this->persist($locale);

        if ($request->isXmlHttpRequest()) {
            return new Response('', 204);
        } else {
            return $this->redirect($this->generateUrl('pim_catalog_locale_index'));
        }
    }
}
