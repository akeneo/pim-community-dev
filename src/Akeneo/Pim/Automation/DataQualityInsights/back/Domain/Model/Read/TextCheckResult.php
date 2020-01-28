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

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Read;

/**
 * @author Olivier Pontier <olivier.pontier@akeneo.com>
 */
class TextCheckResult
{
    const SPELLING_ISSUE_TYPE = 'misspelling';

    /**
     * @var string
     */
    private $text;
    /**
     * @var string
     */
    private $type;
    /**
     * @var int
     */
    private $globalOffset;
    /**
     * @var int
     */
    private $offset;
    /**
     * @var int
     */
    private $line;
    /**
     * @var array
     */
    private $suggestions;

    public function __construct(string $text, string $type, int $globalOffset, int $offset, int $line, array $suggestions)
    {
        $this->text = $text;
        $this->type = $type;
        $this->globalOffset = $globalOffset;
        $this->offset = $offset;
        $this->line = $line;
        $this->suggestions = $suggestions;
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return int
     */
    public function getGlobalOffset(): int
    {
        return $this->globalOffset;
    }

    /**
     * @return int
     */
    public function getOffset(): int
    {
        return $this->offset;
    }

    /**
     * @return int
     */
    public function getLine(): int
    {
        return $this->line;
    }

    /**
     * @return array
     */
    public function getSuggestions(): array
    {
        return $this->suggestions;
    }

    public function normalize(): array
    {
        return [
            'text' =>  $this->getText(),
            'type' => $this->getType(),
            'globalOffset' => $this->getGlobalOffset(),
            'offset' => $this->getOffset(),
            'line' => $this->getLine(),
            'suggestions' => $this->getSuggestions(),
        ];
    }
}
