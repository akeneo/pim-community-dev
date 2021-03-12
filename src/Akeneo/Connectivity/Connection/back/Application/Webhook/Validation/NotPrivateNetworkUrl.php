<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Application\Webhook\Validation;

use Symfony\Component\Validator\Constraint;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NotPrivateNetworkUrl extends Constraint
{
    public $unresolvableHostMessage = 'Could not resolve host {{ host }}.';
    public $ipBlockedMessage = 'IP {{ ip }} is blocked for {{ url }}.';
}
