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

namespace Akeneo\Category\Domain\Model;

use Akeneo\Category\Domain\ValueObject\Attribute\AttributeCode;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeIdentifier;
use Akeneo\Category\Domain\ValueObject\Attribute\AttributeOrder;
use Akeneo\Category\Domain\ValueObject\TemplateIdentifier;


interface Attribute
{

    public function getIdentifier(): AttributeIdentifier;

    public function getTemplateIdentifier(): TemplateIdentifier;

    public function getCode(): AttributeCode;

    public function getLabel(string $localeCode): ?string;

    public function getLabelCodes(): array;

    public function getOrder(): AttributeOrder;

    public function normalize(): array;

    public function getType(): string;
}
