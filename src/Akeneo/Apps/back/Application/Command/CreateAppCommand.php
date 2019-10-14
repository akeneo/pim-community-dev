<?php

declare(strict_types=1);

namespace Akeneo\Apps\Application\Command;

use Akeneo\Apps\Domain\Model\AppCode;
use Akeneo\Apps\Domain\Model\FlowType;

/**
 * @author Romain Monceau <romain@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class CreateAppCommand
{
    private $appCode;
    private $appLabel;
    private $flowType;

    public function __construct(AppCode $appCode, string $appLabel, FlowType $flowType)
    {
        $this->appCode = $appCode;
        $this->appLabel = $appLabel;
        $this->flowType = $flowType;
    }

    public function appCode(): AppCode
    {
        return $this->appCode;
    }

    public function appLabel(): string
    {
        return $this->appLabel;
    }

    public function flowType(): FlowType
    {
        return $this->flowType;
    }
}
