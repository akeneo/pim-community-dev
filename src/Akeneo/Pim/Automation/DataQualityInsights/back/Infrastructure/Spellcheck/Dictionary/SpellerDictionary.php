<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Spellcheck\Dictionary;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/MIT MIT
 * @source    https://github.com/mekras/php-speller
 */
class SpellerDictionary
{
    /** @var string */
    private $dictionaryPath;

    /**
     * @param string $path
     */
    public function __construct(string $path)
    {
        $this->dictionaryPath = $path;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->dictionaryPath;
    }
}
