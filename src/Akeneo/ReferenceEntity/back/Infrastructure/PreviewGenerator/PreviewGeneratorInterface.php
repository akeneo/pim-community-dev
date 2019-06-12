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

namespace Akeneo\ReferenceEntity\Infrastructure\PreviewGenerator;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\UrlAttribute;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
interface PreviewGeneratorInterface
{
    public function supports(string $data, UrlAttribute $attribute, string $type): bool;

    public function generate(string $data, UrlAttribute $attribute, string $type): string;
}
