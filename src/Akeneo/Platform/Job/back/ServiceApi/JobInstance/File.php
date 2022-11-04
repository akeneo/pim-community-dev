<?php

declare(strict_types=1);

namespace Akeneo\Platform\Job\ServiceApi\JobInstance;

use Webmozart\Assert\Assert;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license https://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class File
{
    /**
     * @param resource $resource
     */
    public function __construct(
        private string $fileName,
        private $resource,
    ) {
        Assert::resource($resource, 'stream');
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    /**
     * @return resource
     */
    public function getResource()
    {
        return $this->resource;
    }
}
