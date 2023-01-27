<?php

declare(strict_types=1);

namespace Akeneo\Category\back\tests\Integration\Infrastructure\Storage\Sql;

use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;
use Akeneo\Category\Domain\ValueObject\Code;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DeleteTemplateAndAttributesSqlIntegration extends CategoryTestCase
{
    public function TestDeleteTemplateAndAttributes(): void
    {
        $category = $this->insertBaseCategory(new Code('template_deletion'));
        $template = $this->givenTemplateWithAttributes(
            '7b26de6a-9e64-11ed-a8fc-0242ac120002',
            $category->getId()
        );
    }
}
