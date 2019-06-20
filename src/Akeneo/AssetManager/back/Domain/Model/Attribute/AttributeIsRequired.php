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

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeIsRequired
{
    /** @var bool */
    private $value;

    private function __construct(bool $value)
    {
        $this->value = $value;
    }

    public static function fromBoolean(bool $isRequired): self
    {
        return new self($isRequired);
    }

    public function normalize(): bool
    {
        return $this->value;
    }
}
