<?php

namespace Akeneo\Channel\Infrastructure\Controller\InternalApi;

use Akeneo\Channel\Infrastructure\Component\Repository\CurrencyRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Currency rest controller
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CurrencyController
{
    public function __construct(
        private CurrencyRepositoryInterface $currencyRepository,
    ) {
    }

    public function indexAction(): JsonResponse
    {
        $currencies = $this->currencyRepository->getActivatedCurrencies();

        $normalizedCurrencies = [];
        foreach ($currencies as $currency) {
            $normalizedCurrencies[$currency->getCode()] = ['code' => $currency->getCode()];
        }

        return new JsonResponse($normalizedCurrencies);
    }
}
