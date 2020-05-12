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

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Aspell\Source;

use Mekras\Speller\Source\EncodingAwareSource;
use Mekras\Speller\Source\Filter\HtmlFilter;
use Mekras\Speller\Source\StringSource;

/**
 * @author Olivier Pontier <olivier.pontier@akeneo.com>
 */
class TextSource implements EncodingAwareSource
{
    const DEFAULT_ENCODING = 'UTF-8';

    private $source;

    private $sourceAsString;

    public function __construct(string $source, string $encoding = self::DEFAULT_ENCODING)
    {
        $this->source = new StringSource($source, $encoding);

        $this->sourceAsString = $this->source->getAsString();

        if ($this->isHtml($this->sourceAsString)) {
            $filter = new HtmlFilter();
            $this->sourceAsString = $filter->filter($this->sourceAsString);
        }
    }

    public function getAsString(): string
    {
        return $this->sourceAsString;
    }

    public function getEncoding(): string
    {
        return $this->source->getEncoding();
    }

    private function isHtml(string $text)
    {
        // @todo[DAPI-634] test it and improve it
        return preg_match('~<\s?[^\>]*/?\s?>~i', $text);
    }
}
