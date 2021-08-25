<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\FreeTrial\Infrastructure\Install\Reader;

use Akeneo\FreeTrial\Infrastructure\Install\InstallCatalogTrait;

final class JsonFileReader implements FixtureReader
{
    use InstallCatalogTrait;

    private string $getFilePath;

    public function __construct(string $getFilePath)
    {
        $this->getFilePath = $getFilePath;
    }

    public function read(): \Iterator
    {
        // @fixme: avoid to use a method name as parameter
        $getFilePath = $this->getFilePath;
        $filePath = $this->$getFilePath();

        $sourceFile = fopen($filePath, 'r');

        if (false === $sourceFile) {
            throw new \Exception(sprintf('Failed to open source file "%s"', $filePath));
        }

        while ($data = fgets($sourceFile)) {
            yield json_decode($data, true);
        }
    }
}
