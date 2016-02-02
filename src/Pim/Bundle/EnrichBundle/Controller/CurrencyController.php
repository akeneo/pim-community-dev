<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\CatalogBundle\Entity\Currency;
use Pim\Bundle\CatalogBundle\Exception\LinkedChannelException;
use Pim\Bundle\EnrichBundle\Flash\Message;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

/**
 * Currency controller for configuration
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CurrencyController
{
    /** @var SaverInterface */
    protected $currencySaver;

    /** @var Request */
    protected $request;

    /** @var RouterInterface */
    protected $router;

    /**
     * @param Request         $request
     * @param RouterInterface $router
     * @param SaverInterface  $currencySaver
     */
    public function __construct(Request $request, RouterInterface $router, SaverInterface $currencySaver)
    {
        $this->currencySaver = $currencySaver;
        $this->request       = $request;
        $this->router        = $router;
    }

    /**
     * List currencies
     *
     * @param Request $request
     *
     * @Template
     * @AclAncestor("pim_enrich_currency_index")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request)
    {
        return [];
    }

    /**
     * Activate/Deactivate a currency
     *
     * @param Currency $currency
     *
     * @AclAncestor("pim_enrich_currency_toggle")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function toggleAction(Currency $currency)
    {
        try {
            $currency->toggleActivation();
            $this->currencySaver->save($currency);

            $this->request->getSession()->getFlashBag()->add('success', new Message('flash.currency.updated'));
        } catch (LinkedChannelException $e) {
            $this->request->getSession()->getFlashBag()
                ->add('error', new Message('flash.currency.error.linked_to_channel'));
        } catch (\Exception $e) {
            $this->request->getSession()->getFlashBag()->add('error', new Message('flash.error ocurred'));
        }

        return new RedirectResponse($this->router->generate('pim_enrich_currency_index'));
    }
}
