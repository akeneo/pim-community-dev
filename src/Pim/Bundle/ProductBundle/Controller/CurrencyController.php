<?php

namespace Pim\Bundle\ProductBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Pim\Bundle\ProductBundle\Controller\Controller;
use Pim\Bundle\ProductBundle\Entity\Currency;

/**
 * Currency controller for configuration
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CurrencyController extends Controller
{
    /**
     * List currencies
     *
     * @param Request $request
     *
     * @Template()
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        /** @var $queryBuilder QueryBuilder */
        $queryBuilder = $this->getManager()->createQueryBuilder();
        $queryBuilder
            ->select('c')
            ->from('PimProductBundle:Currency', 'c');

        /** @var $queryFactory QueryFactory */
        $queryFactory = $this->get('pim_product.datagrid.manager.currency.default_query_factory');
        $queryFactory->setQueryBuilder($queryBuilder);

        /** @var $datagridManager LocaleDatagridManager */
        $datagridManager = $this->get('pim_product.datagrid.manager.currency');
        $datagrid = $datagridManager->getDatagrid();

        $view = ('json' === $request->getRequestFormat()) ?
            'OroGridBundle:Datagrid:list.json.php' : 'PimProductBundle:Currency:index.html.twig';

        return $this->render($view, array('datagrid' => $datagrid->createView()));
    }

    /**
     * Activate/Desactivate a currency
     *
     * @param Currency $currency
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function toggleAction(Currency $currency)
    {
        try {
            $currency->toggleActivation();
            $this->flush();

            $this->addFlash('success', 'Currency is successfully updated.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Action failed. Please retry.');
        }

        return $this->redirect($this->generateUrl('pim_product_currency_index'));
    }
}
