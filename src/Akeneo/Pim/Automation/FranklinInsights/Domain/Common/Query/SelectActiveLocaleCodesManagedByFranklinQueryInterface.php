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

namespace Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Query;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Common\Model\Read\LocaleCode;

/**
 * @author Romain Monceau <romain@akeneo.com>
 */
interface SelectActiveLocaleCodesManagedByFranklinQueryInterface
{
    /**
     * @return LocaleCode[]
     */
    public function execute(): array;
}
