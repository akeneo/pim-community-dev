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

namespace Akeneo\ReferenceEntity\Domain\Model\Attribute;

use Webmozart\Assert\Assert;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeAllowedExtensions
{
    public const ALL_ALLOWED = [];
    public const VALID_EXTENSIONS = ['gif', 'jfif', 'jif', 'jpeg', 'jpg', 'pdf', 'png', 'psd', 'tif', 'tiff'];

    /** @var string[] */
    private $allowedExtensions;

    private function __construct(array $allowedExtensions)
    {
        array_walk($allowedExtensions, function ($allowedExtension) {
            Assert::string($allowedExtension, 'Expected allowed extension to be a string');
            Assert::true(
                in_array($allowedExtension, self::VALID_EXTENSIONS),
                sprintf(
                    'Expected extensions to be any of "%s", "%s" given',
                    implode(', ', self::VALID_EXTENSIONS),
                    $allowedExtension
                )
            );
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
