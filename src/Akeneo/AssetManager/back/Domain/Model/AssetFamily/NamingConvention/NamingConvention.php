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

namespace Akeneo\AssetManager\Domain\Model\AssetFamily\NamingConvention;

use Webmozart\Assert\Assert;

/**
 * This class represents a naming convention used to automatically fill asset values at the creation step.
 * The "source" (either the asset code or a media file attribute code -in the latter case the filename will be parsed)
 * is parsed with the given "pattern". If the "strict" mode is activated, any failure in the parsing will result in an
 * error, else it will silently fail.
 */
class NamingConvention implements NamingConventionInterface
{
    /** @var Source */
    private $source;

    /** @var Pattern */
    private $pattern;

    /** @var bool */
    private $strict;

    private function __construct(Source $source, Pattern $pattern, bool $strict)
    {
        $this->source = $source;
        $this->pattern = $pattern;
        $this->strict = $strict;
    }

    public static function createFromNormalized(array $normalizedNamingConvention): NamingConventionInterface
    {
        if ([] === $normalizedNamingConvention) {
            return new NullNamingConvention();
        }

        Assert::keyExists($normalizedNamingConvention, 'source');
        Assert::isArray($normalizedNamingConvention['source']);
        Assert::keyExists($normalizedNamingConvention, 'pattern');
        Assert::string($normalizedNamingConvention['pattern']);
        Assert::keyExists($normalizedNamingConvention, 'strict');
        Assert::boolean($normalizedNamingConvention['strict']);

        return new self(
            Source::createFromNormalized($normalizedNamingConvention['source']),
            Pattern::create($normalizedNamingConvention['pattern']),
            $normalizedNamingConvention['strict']
        );
    }

    public function normalize(): array
    {
        return [
            'source' => $this->source->normalize(),
            'pattern' => $this->pattern->normalize(),
            'strict' => $this->strict
        ];
    }
}
