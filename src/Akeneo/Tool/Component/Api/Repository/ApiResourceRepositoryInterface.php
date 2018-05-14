<?php

namespace Akeneo\Tool\Component\Api\Repository;

use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;

/**
 * This interface should be used for simple entities on the API,
 * when the only need is to paginate and require a single resource.
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface ApiResourceRepositoryInterface extends PageableRepositoryInterface, IdentifiableObjectRepositoryInterface
{
}
