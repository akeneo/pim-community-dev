<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Tests\EndToEnd;

use GuzzleHttp\Psr7\Message;
use Webmozart\Assert\Assert;

/**
 * @author JMLeroux <jean-marie.leroux@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *
 * A filesystem container to store Guzzle history in a json file.
 * We need this filesytem json file to share it between process and keep track of the history in the tests
 */
class GuzzleJsonHistoryContainer implements \ArrayAccess, \Countable
{
    public function __construct(private string $filepath)
    {
    }

    public function resetHistory(): void
    {
        if (\file_exists($this->filepath)) {
            \unlink($this->filepath);
        }
    }

    public function offsetExists(mixed $offset): bool
    {
        $history = $this->readFile();

        return isset($history[$offset]);
    }

    public function offsetGet(mixed $offset): mixed
    {
        $history = $this->readFile();
        Assert::isArray($history);

        return $history[$offset];
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
        $history = $this->readFile();
        $history[] = [
            'request' => Message::toString($value['request']),
            'response' => $value['response'] ? Message::toString($value['response']) : null,
        ];
        $this->writeFile($history);
    }

    public function offsetUnset(mixed $offset): void
    {
        $history = $this->readFile();
        unset($history[$offset]);
        $this->writeFile($history);
    }

    public function count(): int
    {
        $history = $this->readFile();

        return \count($history);
    }

    private function readFile(): array
    {
        if (!\file_exists($this->filepath)) {
            return [];
        }

        return \json_decode(\file_get_contents($this->filepath), true, 512, JSON_THROW_ON_ERROR);
    }

    private function writeFile(array $history): void
    {
        \file_put_contents($this->filepath, \json_encode($history, JSON_THROW_ON_ERROR));
    }
}
