<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Akeneo\Component\StorageUtils\Saver\SaverInterface;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
use Pim\Bundle\CatalogBundle\Entity\Currency;
use Pim\Bundle\EnrichBundle\Flash\Message;
use Pim\Component\Catalog\Exception\LinkedChannelException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
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
    /** @var RequestStack */
    protected $requestStack;

    /** @var RouterInterface */
    protected $router;

    /** @var SaverInterface */
    protected $currencySaver;

    /**
     * @param RequestStack    $requestStack
     * @param RouterInterface $router
     * @param SaverInterface  $currencySaver
     */
    public function __construct(RequestStack $requestStack, RouterInterface $router, SaverInterface $currencySaver)
    {
        $this->requestStack = $requestStack;
        $this->router = $router;
        $this->currencySaver = $currencySaver;
    }

    /**
     * Activate/Deactivate a currency
     *
     * @param Currency $currency
     *
     * @AclAncestor("pim_enrich_currency_toggle")
     *
     * @return JsonResponse
     */
    public function toggleAction(Currency $currency)
    {
        $request = $this->requestStack->getCurrentRequest();

        try {
            $currency->toggleActivation();
            $this->currencySaver->save($currency);

            $request
                ->getSession()
                ->getFlashBag()
                ->add('success', new Message('flash.currency.updated'));
        } catch (LinkedChannelException $e) {
            $request
                ->getSession()
                ->getFlashBag()
                ->add('error', new Message('flash.currency.error.linked_to_channel'));
        } catch (\Exception $e) {
            $request
                ->getSession()
                ->getFlashBag()
                ->add('error', new Message('flash.error ocurred'));
        }

        return new JsonResponse(['route' => 'pim_enrich_currency_index']);
    }
}
