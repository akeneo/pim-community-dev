<?php
declare(strict_types=1);

namespace Akeneo\Category\Domain\Component\Manager;

use Akeneo\Category\Domain\Component\Classification\Model\CategoryInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface PositionResolverInterface
{
    public function getPosition(CategoryInterface $category): int;
}
