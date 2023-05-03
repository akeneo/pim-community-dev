<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Command\DeleteCategoryCommand;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class DeleteCategoryCommand
{
    public function __construct(public readonly int $id)
    {
    }
}
