<?php

declare(strict_types=1);

namespace Akeneo\Pim\Automation\IdentifierGenerator\Application\Create;

use Akeneo\Pim\Automation\IdentifierGenerator\Application\CommandInterface;
use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class CreateGeneratorCommand implements CommandInterface
{
    /**
     * @param string $code
     * @param array<mixed> $conditions
     * @param array<mixed> $structure
     * @param array<string, string> $labels
     * @param string $target
     * @param string|null $delimiter
     */
    public function __construct(
        public string $code,
        public array $conditions,
        public array $structure,
        public array $labels,
        public string $target,
        public ?string $delimiter,
    ) {
    }

    /**
     * @param array<string, mixed> $normalizedGenerator
     */
    public static function fromNormalized(array $normalizedGenerator): self
    {
        foreach (['code', 'conditions', 'structure', 'labels', 'target', 'delimiter'] as $key) {
            Assert::keyExists($normalizedGenerator, $key);
        }
        Assert::string($normalizedGenerator['code']);
        Assert::isArray($normalizedGenerator['conditions']);
        Assert::isArray($normalizedGenerator['structure']);
        Assert::isArray($normalizedGenerator['labels']);
        Assert::string($normalizedGenerator['target']);
        Assert::nullOrString($normalizedGenerator['delimiter']);

        return new self(
            $normalizedGenerator['code'],
            $normalizedGenerator['conditions'],
            $normalizedGenerator['structure'],
            $normalizedGenerator['labels'],
            $normalizedGenerator['target'],
            $normalizedGenerator['delimiter']
        );
    }
}
