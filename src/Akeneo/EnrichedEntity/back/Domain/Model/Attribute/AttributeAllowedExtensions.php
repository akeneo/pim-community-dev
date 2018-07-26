<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Domain\Model\Attribute;

use Webmozart\Assert\Assert;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeAllowedExtensions
{
    /** @var string[] */
    private $allowedExtensions;

    public function __construct(array $allowedExtensions)
    {
        array_map(function ($allowedExtension) {
            Assert::string($allowedExtension, 'Expected allowed extension to be a string');
        }, $allowedExtensions);
        $this->allowedExtensions = $allowedExtensions;
    }

    public static function fromList(array $allowedExtensions) : self
    {
        return new self($allowedExtensions);
    }

    public function normalize(): array
    {
        return $this->allowedExtensions;
    }
}
