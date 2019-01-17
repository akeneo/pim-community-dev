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

namespace Akeneo\ReferenceEntity\Common\Fake;

use Akeneo\ReferenceEntity\Domain\Query\File\FindFileDataByFileKeyInterface;
use Webmozart\Assert\Assert;

class InMemoryFindFileDataByFileKey implements FindFileDataByFileKeyInterface
{
    /** @var array */
    private $files = [];

    public function __invoke(string $fileKey): ?array
    {
        return $this->files[$fileKey] ?? null;
    }

    public function save(array $fileData): void
    {
        Assert::keyExists($fileData, 'filePath');

        $this->files[$fileData['filePath']] = $fileData;
    }
}
