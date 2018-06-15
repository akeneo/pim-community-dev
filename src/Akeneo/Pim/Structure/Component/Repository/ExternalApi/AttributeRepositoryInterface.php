<?php

namespace Akeneo\Pim\Structure\Component\Repository\ExternalApi;

use Akeneo\Tool\Component\Api\Repository\PageableRepositoryInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;

/**
 * Repository interface for attributes resources
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface AttributeRepositoryInterface extends PageableRepositoryInterface, IdentifiableObjectRepositoryInterface
{
    /**
     * Get identifier code
     *
     * @return string
     */
    public function getIdentifierCode();

    /**
     * Get media attribute codes
     *
     * @return string[]
     */
    public function getMediaAttributeCodes();
}
