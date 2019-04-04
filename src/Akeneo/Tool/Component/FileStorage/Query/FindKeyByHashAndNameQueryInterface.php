<?php

namespace Akeneo\Tool\Component\FileStorage\Query;

/**
 * Find a FileInfo model by its hash value
 *
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FindKeyByHashAndNameQueryInterface
{
    public function fetchKey(string $hash, string $originalFilename): ?string;
}
