<?php
declare(strict_types=1);

namespace Akeneo\UserManagement\Bundle\Datagrid\Action;

use Oro\Bundle\DataGridBundle\Extension\Action\Actions\NavigateAction;

/**
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
final class DuplicateUserAction extends NavigateAction
{
    protected $requiredOptions = ['propertyName'];
}
