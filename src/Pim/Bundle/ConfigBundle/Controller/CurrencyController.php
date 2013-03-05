<?php

namespace Pim\Bundle\ConfigBundle\Controller;

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
     * @return multitype
     *
     * @Route("/index")
     * @Template()
     */
    public function indexAction()
    {
        $currencies = $this->getCurrencyRepository()->findAll();

        return array('currencies' => $currencies);
    }

    /**
     * Get storage manager
     *
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    protected function getStorageManager()
    {
        return $this->container->get('doctrine.orm.entity_manager');
    }

    /**
     * Get Currency Repository
     *
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    protected function getCurrencyRepository()
    {
        return $this->getStorageManager()->getRepository('PimConfigBundle:Currency');
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
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function disableAction(Currency $currency)
    {
        // Disable activated property if no language associated
        if ($currency->getLanguages()->count() === 0) {
            $currency->setActivated(false);
            $this->getStorageManager()->persist($currency);
            $this->getStorageManager()->flush();

            $this->get('session')->getFlashBag()->add('success', 'Currency successfully disable');
        } else {
            $this->get('session')->getFlashBag()->add('info', 'Currency linked to languages... Can\'t be disabled');
        }

        return $this->redirect($this->generateUrl('pim_config_currency_index'));
    }
}
