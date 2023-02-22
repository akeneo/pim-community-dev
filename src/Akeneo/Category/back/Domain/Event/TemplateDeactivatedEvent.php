<?php

declare(strict_types=1);

namespace Akeneo\Category\Domain\Event;

use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class TemplateDeactivatedEvent
{
    public function __construct(private readonly TemplateUuid $templateUuid)
    {
    }

    public function getTemplateUuid(): TemplateUuid
    {
        return $this->templateUuid;
    }
}
