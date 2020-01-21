<?php

namespace Akeneo\Channel\Bundle\Controller\UI;

use Akeneo\Channel\Component\Exception\LinkedChannelException;
use Akeneo\Channel\Component\Model\Currency;
use Akeneo\Platform\Bundle\UIBundle\Flash\Message;
use Akeneo\Tool\Component\StorageUtils\Saver\SaverInterface;
use Oro\Bundle\SecurityBundle\Annotation\AclAncestor;
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
     * todo merge remove RequestStack from the constructor
     *
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
        try {
            $currency->toggleActivation();
            $this->currencySaver->save($currency);
        } catch (LinkedChannelException $e) {
            return new JsonResponse([
                'successful' => false,
                'message' => 'flash.currency.error.linked_to_channel'
            ]);
        }

        return new JsonResponse([
            'successful' => true,
            'message' => 'flash.currency.updated'
        ]);
    }
}
