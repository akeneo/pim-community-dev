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
     * @param string $id
     * @param string $code
     * @param array[] $conditions
     * @param array[] $structure
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

    public static function fromNormalized(array $content): self
    {
        foreach(['code', 'conditions', 'structure', 'labels', 'target', 'delimiter'] as $key) {
            Assert::keyExists($content, $key);
        }
        Assert::stringNotEmpty($content['code']);
        Assert::isArray($content['conditions']);
        Assert::isArray($content['structure']);
        Assert::isArray($content['labels']);
        Assert::stringNotEmpty($content['target']);
        Assert::nullOrString($content['delimiter']);

        return new self(
            $content['code'],
            $content['conditions'],
            $content['structure'],
            $content['labels'],
            $content['target'],
            $content['delimiter']
        );
    }
}
