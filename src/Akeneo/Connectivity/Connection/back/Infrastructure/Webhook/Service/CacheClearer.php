<?php
declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Webhook\Service;

use Akeneo\Channel\Bundle\Doctrine\Query\FindActivatedCurrencies;
use Akeneo\Channel\Component\Query\PublicApi\ChannelExistsWithLocaleInterface;
use Akeneo\Connectivity\Connection\Application\Webhook\Service\CacheClearerInterface;
use Akeneo\Pim\Structure\Bundle\Query\PublicApi\Attribute\Cache\LRUCachedGetAttributes;
use Akeneo\Tool\Bundle\ConnectorBundle\Doctrine\UnitOfWorkAndRepositoriesClearer;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CacheClearer implements CacheClearerInterface
{
    private ChannelExistsWithLocaleInterface $channelExistsWithLocale;
    private FindActivatedCurrencies $findActivatedCurrencies;
    private UnitOfWorkAndRepositoriesClearer $unitOfWorkAndRepositoriesClearer;
    private LRUCachedGetAttributes $LRUCachedGetAttributes;

    public function __construct(
        ChannelExistsWithLocaleInterface $channelExistsWithLocale,
        FindActivatedCurrencies $findActivatedCurrencies,
        UnitOfWorkAndRepositoriesClearer $unitOfWorkAndRepositoriesClearer,
        LRUCachedGetAttributes $LRUCachedGetAttributes
    ) {
        $this->channelExistsWithLocale = $channelExistsWithLocale;
        $this->findActivatedCurrencies = $findActivatedCurrencies;
        $this->unitOfWorkAndRepositoriesClearer = $unitOfWorkAndRepositoriesClearer;
        $this->LRUCachedGetAttributes = $LRUCachedGetAttributes;
    }

    public function clear(): void
    {
        $this->channelExistsWithLocale->clearCache();
        $this->findActivatedCurrencies->clearCache();
        $this->unitOfWorkAndRepositoriesClearer->clear();
        $this->LRUCachedGetAttributes->clearCache();
    }
}
