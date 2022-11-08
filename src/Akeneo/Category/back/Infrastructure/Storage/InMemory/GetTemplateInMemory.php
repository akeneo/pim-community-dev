<?php

declare(strict_types=1);

namespace Akeneo\Category\Infrastructure\Storage\InMemory;

use Akeneo\Category\Application\Query\GetTemplate;
use Akeneo\Category\Domain\Model\Enrichment\Template;
use Akeneo\Category\Domain\ValueObject\CategoryId;
use Akeneo\Category\Domain\ValueObject\LabelCollection;
use Akeneo\Category\Domain\ValueObject\Template\TemplateCode;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class GetTemplateInMemory implements GetTemplate
{
    const TEMPLATE_UUID_IN_MEMORY = '02274dac-e99a-4e1d-8f9b-794d4c3ba330';

    public function byUuid(TemplateUuid $uuid): ?Template
    {
        $uuid = TemplateUuid::fromString(self::TEMPLATE_UUID_IN_MEMORY);

        return new Template(
            uuid: $uuid,
            code: new TemplateCode('default_template'),
            labelCollection: LabelCollection::fromArray(['en_US' => 'Default template']),
            categoryTreeId: new CategoryId(1),
            attributeCollection: null
        );
    }
}
