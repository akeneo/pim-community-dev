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

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Spellcheck\Source;

use Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Spellcheck\Filter\HTMLFilter;

/**
 * @author Olivier Pontier <olivier.pontier@akeneo.com>
 */
class TextSource
{
    public const DEFAULT_ENCODING = 'UTF-8';

    /**
     * @var string The filtered string if $source is html
     */
    private string $sourceAsString;

    public function __construct(
        string $source,
        private string $encoding = self::DEFAULT_ENCODING
    ) {
        if ($this->isHtml($source)) {
            $this->sourceAsString = (new HTMLFilter())->filter($source);
        } else {
            $this->sourceAsString = $source;
        }
    }

    public function getAsString(): string
    {
        return $this->sourceAsString;
    }

    public function getEncoding(): string
    {
        return $this->encoding;
    }

    private function isHtml(string $text): bool|int
    {
        return preg_match('~<\s?[^\>]*/?\s?>~i', $text);
    }
}
