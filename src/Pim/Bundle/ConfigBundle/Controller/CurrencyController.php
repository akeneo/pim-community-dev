<?php

namespace Pim\Bundle\ConfigBundle\Controller;

use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\HttpFoundation\Request;

use Pim\Bundle\ConfigBundle\Entity\Currency;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Currency controller for configuration
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Route("/currency")
 */
class CurrencyController extends Controller
{

    /**
     * List currencies
     *
     * @param Request $request
     *
     * @Route(
     *     "/index.{_format}",
     *     requirements={"_format"="html|json"},
     *     defaults={"_format" = "html"}
     * )
     * @Template()
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        /** @var $queryBuilder QueryBuilder */
        $queryBuilder = $this->getEntityManager()->createQueryBuilder();
        $queryBuilder
            ->select('c')
            ->from('PimConfigBundle:Currency', 'c');

        /** @var $queryFactory QueryFactory */
        $queryFactory = $this->get('pim_config.datagrid.manager.currency.default_query_factory');
        $queryFactory->setQueryBuilder($queryBuilder);

        /** @var $datagridManager LocaleDatagridManager */
        $datagridManager = $this->get('pim_config.datagrid.manager.currency');
        $datagrid = $datagridManager->getDatagrid();

        $view = ('json' === $request->getRequestFormat()) ?
            'OroGridBundle:Datagrid:list.json.php' : 'PimConfigBundle:Currency:index.html.twig';

        return $this->render($view, array('datagrid' => $datagrid->createView()));
    }

    /**
     * Get entity manager
     *
     * @return \Doctrine\ORM\EntityManager
     */
    protected function getEntityManager()
    {
        return $this->getDoctrine()->getEntityManager();
    }

    /**
     * Get currency repository
     *
     * @return \Doctrine\ORM\EntityRepository
     */
    protected function getCurrencyRepository()
    {
        return $this->getEntityManager()->getRepository('PimConfigBundle:Currency');
    }

    /**
     * Create currency
     *
     * @Route("/create")
     * @Template("PimConfigBundle:Currency:edit.html.twig")
     *
     * @return array
     */
    public function createAction()
    {
        $currency = new Currency();

        return $this->editAction($currency);
    }

    /**
     * Edit currency
     *
     * @param Currency $currency
     *
     * @Route("/edit/{id}", requirements={"id"="\d+"}, defaults={"id"=0})
     * @Template
     *
     * @return array
     */
    public function editAction(Currency $currency)
    {
        if ($this->get('pim_config.form.handler.currency')->process($currency)) {
            $this->get('session')->getFlashBag()->add('success', 'Currency successfully saved');

            return $this->redirect(
                $this->generateUrl('pim_config_currency_index')
            );
        }

        return array(
            'form' => $this->get('pim_config.form.currency')->createView()
        );
    }

    /**
     * Disable currency
     *
     * @param Currency $currency
     *
     * @Route("/disable/{id}", requirements={"id"="\d+"})
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function disableAction(Currency $currency)
    {
        // Disable activated property if no locale associated
        if ($currency->getLocales()->count() === 0) {
            $currency->setActivated(false);
            $this->getEntityManager()->persist($currency);
            $this->getEntityManager()->flush();

            if ($this->getRequest()->isXmlHttpRequest()) {
                return new Response('', 204);
            } else {
                return $this->redirect($this->generateUrl('pim_config_currency_index'));
            }
        } else {
            return new Response('Currency linked to locales. Can`\t be disabled', 500);
        }
    }
}
