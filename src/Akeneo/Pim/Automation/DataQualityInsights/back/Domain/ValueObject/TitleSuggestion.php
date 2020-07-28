<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\ValueObject;

/**
 * @author Olivier Pontier <olivier.pontier@akeneo.com>
 */
class TitleSuggestion
{
    private $title;

    public function __construct(string $title)
    {
        if (empty($title)) {
            throw new \InvalidArgumentException('Title suggestion should not be empty');
        }

        $this->title = $title;
    }

    public function __toString()
    {
        return $this->title;
    }
}
