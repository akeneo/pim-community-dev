<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Persistence\Currency;

use Akeneo\Catalogs\Application\Persistence\Currency\IsCurrencyActivatedQueryInterface;
use Akeneo\Channel\Infrastructure\Component\Repository\CurrencyRepositoryInterface;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IsCurrencyActivatedQuery implements IsCurrencyActivatedQueryInterface
{
    public function __construct(
        private CurrencyRepositoryInterface $currencyRepository,
    ) {
    }

    public function execute(string $code): bool
    {
        $activatedCurrenciesCodes = $this->currencyRepository->getActivatedCurrencyCodes();

        return \in_array($code, $activatedCurrenciesCodes);
    }
}
