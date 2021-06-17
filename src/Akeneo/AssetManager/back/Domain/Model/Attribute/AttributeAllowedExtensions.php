<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\AssetManager\Domain\Model\Attribute;

use Webmozart\Assert\Assert;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeAllowedExtensions
{
    public const ALL_ALLOWED = [];
    public const MAX_EXTENSION_LENGTH = 20;
    public const EXTENSION_SEPARATOR = '.';

    /** @var string[] */
    private array $allowedExtensions;

    private function __construct(array $allowedExtensions)
    {
        Assert::allStringNotEmpty($allowedExtensions, 'Expected allowed extension to be a string');
        array_walk($allowedExtensions, function ($allowedExtension) {
            Assert::notEq(self::EXTENSION_SEPARATOR, $allowedExtension[0], 'Extension should not contain the extension separator.');
            Assert::maxLength($allowedExtension, self::MAX_EXTENSION_LENGTH, sprintf('Extensions cannot be longer than %d characters', self::MAX_EXTENSION_LENGTH));
            Assert::regex($allowedExtension, '/^[a-z0-9]+$/', 'Expected extensions to contain only lowercase letters and numbers');
        });

        $this->allowedExtensions = $allowedExtensions;
    }

    /**
     * @param array $allowedExtensions
     *
     * @return AttributeAllowedExtensions
     */
    public static function fromList(array $allowedExtensions) : self
    {
        return new self($allowedExtensions);
    }

    public function normalize(): array
    {
        return $this->allowedExtensions;
    }

    public function isAllAllowed(): bool
    {
        return $this->allowedExtensions === self:: ALL_ALLOWED;
    }
}
