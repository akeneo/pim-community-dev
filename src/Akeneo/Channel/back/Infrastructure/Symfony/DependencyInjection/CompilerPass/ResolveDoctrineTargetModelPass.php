<?php

declare(strict_types=1);

namespace Akeneo\Channel\Infrastructure\Symfony\DependencyInjection\CompilerPass;

use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Channel\Infrastructure\Component\Model\ChannelTranslationInterface;
use Akeneo\Channel\Infrastructure\Component\Model\CurrencyInterface;
use Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface;
use Akeneo\Tool\Bundle\StorageUtilsBundle\DependencyInjection\Compiler\AbstractResolveDoctrineTargetModelPass;

/**
 * Resolves doctrine ORM Target entities
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ResolveDoctrineTargetModelPass extends AbstractResolveDoctrineTargetModelPass
{
    /**
     * {@inheritdoc}
     */
    protected function getParametersMapping(): array
    {
        return [
            LocaleInterface::class => 'pim_catalog.entity.locale.class',
            ChannelInterface::class => 'pim_catalog.entity.channel.class',
            ChannelTranslationInterface::class => 'pim_catalog.entity.channel_translation.class',
            CurrencyInterface::class => 'pim_catalog.entity.currency.class',
        ];
    }
}
