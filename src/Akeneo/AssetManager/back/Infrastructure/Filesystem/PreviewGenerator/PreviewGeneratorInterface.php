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

namespace Akeneo\AssetManager\Infrastructure\Filesystem\PreviewGenerator;

use Akeneo\AssetManager\Domain\Model\Attribute\AbstractAttribute;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
interface PreviewGeneratorInterface
{
    public function supports(string $data, AbstractAttribute $attribute, string $type): bool;

    /**
     * @param string $data The filename of the external image we want to generate (ex : akeneo.jpg)
     * @param AbstractAttribute $attribute The attribute which need to have a preview
     * @param string $type The format type used to generate the image (ex : dam_thumbnail, dam_preview)
     *
     * @return string Return the URL of the preview generated
     */
    public function generate(string $data, AbstractAttribute $attribute, string $type): string;

    /**
     * @param string $data The filename of the external image we want to generate (ex : akeneo.jpg)
     * @param AbstractAttribute $attribute The attribute which need to have a preview
     * @param string $type The format type used to generate the image (ex : dam_thumbnail, dam_preview)
     */
    public function remove(string $data, AbstractAttribute $attribute, string $type);
}
