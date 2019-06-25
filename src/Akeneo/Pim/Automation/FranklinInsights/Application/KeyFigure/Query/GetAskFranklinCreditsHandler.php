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

namespace Akeneo\Pim\Automation\FranklinInsights\Application\KeyFigure\Query;

use Akeneo\Pim\Automation\FranklinInsights\Domain\KeyFigure\Model\Read\KeyFigure;
use Akeneo\Pim\Automation\FranklinInsights\Domain\KeyFigure\Model\Read\KeyFigureCollection;

final class GetAskFranklinCreditsHandler
{
    public function handle(GetAskFranklinCreditsQuery $query): KeyFigureCollection
    {
        return new KeyFigureCollection(
            [
                new KeyFigure('credits_total', 0),
                new KeyFigure('credits_consumed', 0),
                new KeyFigure('credits_left', 0),
            ]
        );
    }
}
