<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Symfony\DependencyInjection\CompilerPass;

use Akeneo\Category\Infrastructure\Component\Model\CategoryInterface;
use Akeneo\Category\Infrastructure\Component\Model\CategoryTranslationInterface;
use Akeneo\Tool\Bundle\StorageUtilsBundle\DependencyInjection\Compiler\AbstractResolveDoctrineTargetModelPass;

/**
 * Resolves doctrine ORM Target entities.
 *
 * @author    Weasels
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ResolveDoctrineTargetModelPass extends AbstractResolveDoctrineTargetModelPass
{
    protected function getParametersMapping(): array
    {
        return [
            CategoryInterface::class => 'pim_catalog.entity.category.class',
            CategoryTranslationInterface::class => 'pim_catalog.entity.category_translation.class',
        ];
    }
}
