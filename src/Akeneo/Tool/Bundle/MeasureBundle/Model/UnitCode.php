<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\Model;

use Webmozart\Assert\Assert;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class UnitCode
{
    /** @var string */
    private $code;

    private function __construct(string $code)
    {
        Assert::stringNotEmpty($code);
        $this->code = $code;
    }

    public function fromString(string $code): self
    {
        return new self($code);
    }

    public function normalize(): string
    {
        return $this->code;
    }
}
