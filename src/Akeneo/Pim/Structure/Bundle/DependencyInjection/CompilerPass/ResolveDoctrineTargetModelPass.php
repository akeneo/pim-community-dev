<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Bundle\DependencyInjection\CompilerPass;

use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Channel\Component\Model\ChannelTranslationInterface;
use Akeneo\Channel\Component\Model\CurrencyInterface;
use Akeneo\Channel\Component\Model\LocaleInterface;
use Akeneo\Tool\Bundle\StorageUtilsBundle\DependencyInjection\Compiler\AbstractResolveDoctrineTargetModelPass;

/**
 * Resolves doctrine ORM Target entities
 *
 * @author    Arnaud Langlade <arnaud.langlade@gmail.com>
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
        return [];
    }
}
