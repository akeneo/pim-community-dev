<?php

namespace Pim\Bundle\CatalogBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Oro\Bundle\UserBundle\Annotation\Acl;
use Pim\Bundle\CatalogBundle\Entity\Currency;

/**
 * Currency controller for configuration
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Acl(
 *      id="pim_catalog_currency",
 *      name="Currency manipulation",
 *      description="Currency manipulation",
 *      parent="pim_catalog"
 * )
 */
class CurrencyController extends Controller
{
    /**
     * List currencies
     *
     * @param Request $request
     * @Acl(
     *      id="pim_catalog_currency_index",
     *      name="View currency list",
     *      description="View currency list",
     *      parent="pim_catalog_currency"
     * )
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        /** @var $queryBuilder QueryBuilder */
        $queryBuilder = $this->getManager()->createQueryBuilder();
        $queryBuilder
            ->select('c')
            ->from('PimCatalogBundle:Currency', 'c');

        /** @var $queryFactory QueryFactory */
        $queryFactory = $this->get('pim_catalog.datagrid.manager.currency.default_query_factory');
        $queryFactory->setQueryBuilder($queryBuilder);

        /** @var $datagridManager LocaleDatagridManager */
        $datagridManager = $this->get('pim_catalog.datagrid.manager.currency');
        $datagrid = $datagridManager->getDatagrid();

        $view = ('json' === $request->getRequestFormat()) ?
            'OroGridBundle:Datagrid:list.json.php' : 'PimCatalogBundle:Currency:index.html.twig';

        return $this->render($view, array('datagrid' => $datagrid->createView()));
    }

    /**
     * Activate/Desactivate a currency
     *
     * @param Currency $currency
     * @Acl(
     *      id="pim_catalog_currency_toggle",
     *      name="Change currency status",
     *      description="Change currency status",
     *      parent="pim_catalog_currency"
     * )
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
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

        return $this->redirect($this->generateUrl('pim_catalog_currency_index'));
    }
}
