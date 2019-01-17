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

namespace Akeneo\Pim\Automation\FranklinInsights\Domain\IdentifierMapping\Model;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;

/**
 * Identifier Mapping doctrine entity.
 */
final class IdentifierMapping
{
    /** @var int|null */
    private $id;

    /** @var string */
    private $franklinCode;

    /** @var AttributeInterface|null */
    private $attribute;

    /**
     * @param string $franklinCode
     * @param AttributeInterface|null $attribute
     */
    public function __construct(string $franklinCode, ?AttributeInterface $attribute)
    {
        $this->franklinCode = $franklinCode;
        $this->attribute = $attribute;
    }

    /**
     * @return mixed
     */
    public function getFranklinCode(): string
    {
        return $this->franklinCode;
    }

    /**
     * @return mixed
     */
    public function getAttribute(): ?AttributeInterface
    {
        return $this->attribute;
    }
}
