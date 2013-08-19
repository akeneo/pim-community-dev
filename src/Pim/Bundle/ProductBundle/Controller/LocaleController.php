<?php

namespace Pim\Bundle\ProductBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Pim\Bundle\ProductBundle\Entity\Locale;

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
     * @Template
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        /** @var $queryBuilder QueryBuilder */
        $queryBuilder = $this->getManager()->createQueryBuilder();
        $queryBuilder
            ->select('l')
            ->from('PimProductBundle:Locale', 'l');

        /** @var $queryFactory QueryFactory */
        $queryFactory = $this->get('pim_product.datagrid.manager.locale.default_query_factory');
        $queryFactory->setQueryBuilder($queryBuilder);

        /** @var $datagridManager LocaleDatagridManager */
        $datagridManager = $this->get('pim_product.datagrid.manager.locale');
        $datagrid = $datagridManager->getDatagrid();

        $view = ('json' === $request->getRequestFormat()) ?
            'OroGridBundle:Datagrid:list.json.php' : 'PimProductBundle:Locale:index.html.twig';

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
        if ($this->get('pim_product.form.handler.locale')->process($locale)) {
            $this->addFlash('success', 'Locale successfully saved');

            return $this->redirect(
                $this->generateUrl('pim_product_locale_index')
            );
        }

        return array(
            'form' => $this->get('pim_product.form.locale')->createView()
        );
    }

    /**
     * Disable locale
     *
     * @param Locale $locale
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function disableAction(Locale $locale)
    {
        $locale->setActivated(false);
        $this->persist($locale);

        if ($this->getRequest()->isXmlHttpRequest()) {
            return new Response('', 204);
        } else {
            return $this->redirect($this->generateUrl('pim_product_locale_index'));
        }
    }

    /**
     * Enable locale
     *
     * @param Locale $locale
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function enableAction(Locale $locale)
    {
        $locale->setActivated(true);
        $this->persist($locale);

        if ($this->getRequest()->isXmlHttpRequest()) {
            return new Response('', 204);
        } else {
            return $this->redirect($this->generateUrl('pim_product_locale_index'));
        }
    }
}
