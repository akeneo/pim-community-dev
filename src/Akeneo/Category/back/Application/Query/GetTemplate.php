<?php

declare(strict_types=1);

namespace Akeneo\Category\Application\Query;

use Akeneo\Category\Domain\Model\Enrichment\Template;
use Akeneo\Category\Domain\ValueObject\Template\TemplateCode;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
interface GetTemplate
{
    public function byUuid(string $templateId): ?Template;

    public function isAlreadyExistingTemplateCode(TemplateCode $templateCode): bool;
}
