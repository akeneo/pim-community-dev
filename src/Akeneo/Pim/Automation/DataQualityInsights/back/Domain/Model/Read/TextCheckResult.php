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
    /**
     * @var string
     */
    private $word;
    /**
     * @var string
     */
    private $type;
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

    public function __construct(string $word, string $type, ?int $offset, ?int $line, array $suggestions)
    {
        $this->word = $word;
        $this->type = $type;
        $this->offset = $offset;
        $this->line = $line;
        $this->suggestions = $suggestions;
    }

    /**
     * @return string
     */
    public function getWord(): string
    {
        return $this->word;
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
            'word' =>  $this->getWord(),
            'type' => $this->getType(),
            'offset' => $this->getOffset(),
            'line' => $this->getLine(),
            'suggestions' => $this->getSuggestions(),
        ];
    }
}
