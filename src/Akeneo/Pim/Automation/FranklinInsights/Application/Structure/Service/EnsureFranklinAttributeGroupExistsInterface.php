<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\Application\Structure\Service;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
interface EnsureFranklinAttributeGroupExistsInterface
{
    public function ensureExistence(): void;
}
