<?php

declare(strict_types=1);

namespace Akeneo\Pim\WorkOrganization\Workflow\Component\Query\PublicApi;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetWorkflowStatusFromProductIdentifiers
{
    public function fromProductIdentifiers(array $productIdentifiers, int $userId): array;
}
