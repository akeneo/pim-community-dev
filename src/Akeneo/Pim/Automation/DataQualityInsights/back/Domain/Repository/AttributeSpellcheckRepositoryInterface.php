<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\DataQualityInsights\Domain\Repository;

use Akeneo\Pim\Automation\DataQualityInsights\Domain\Model\Structure\AttributeSpellcheck;

interface AttributeSpellcheckRepositoryInterface
{
    public function save(AttributeSpellcheck $attributeSpellcheck): void;

    public function deleteUnknownAttributes(): void;

    public function delete(string $attributeCode): void;
}
