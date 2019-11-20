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

namespace Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation;

use Akeneo\AssetManager\Domain\Model\AssetFamily\Transformation\Operation;
use Webmozart\Assert\Assert;

/**
 * Define the colorspace change of an image.
 */
class ColorspaceOperation implements Operation
{
    private const OPERATION_NAME = 'colorspace';
    private const COLORSPACE_CHOICES = ['grey', 'cmyk', 'rgb'];

    /** @var string */
    private $colorspace;

    private function __construct(string $colorspace)
    {
        Assert::oneOf($colorspace, self::COLORSPACE_CHOICES, sprintf(
            "Parameter 'colorspace' must be one of this values: '%s'. '%s' given.",
            implode(', ', self::COLORSPACE_CHOICES),
            $colorspace
        ));

        $this->colorspace = $colorspace;
    }

    public static function getType(): string
    {
        return self::OPERATION_NAME;
    }

    public static function create(array $parameters): Operation
    {
        Assert::keyExists($parameters, 'colorspace', "Key 'colorspace' must exist in parameters.");
        Assert::string($parameters['colorspace'], "Parameter 'colorspace' must be a string.");

        return new self($parameters['colorspace']);
    }

    public function getColorspace(): string
    {
        return $this->colorspace;
    }

    public function normalize(): array
    {
        return [
            'type' => self::getType(),
            'parameters' => ['colorspace' => $this->colorspace],
        ];
    }
}
