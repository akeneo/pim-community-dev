<?php

namespace Pim\Bundle\CatalogBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Pim\Bundle\CatalogBundle\Entity\Locale;

/**
 * Locale controller for configuration
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LocaleController extends Controller
{
    /**
     * List locales
     *
     * @param Request $request
     *
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
     *
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
     *
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
