<?php

declare(strict_types=1);

namespace Akeneo\Category\back\tests\Integration\Infrastructure\Storage\Sql;

use Akeneo\Category\Application\ActivateTemplate;
use Akeneo\Category\Application\Query\DeactivateTemplate;
use Akeneo\Category\back\tests\Integration\Helper\CategoryTestCase;
use Akeneo\Category\Domain\ValueObject\Code;
use Akeneo\Category\Domain\ValueObject\Template\TemplateUuid;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DeactivateTemplateSqlIntegration extends CategoryTestCase
{
    public function testTemplateHasBeenDeactivated(): void
    {
        $category = $this->insertBaseCategory(new Code('template_deactivation'));
        $mockedTemplate = $this->generateMockedCategoryTemplateModel(
            categoryTreeId: $category->getId()->getValue()
        );

        $activateTemplate = $this->get(ActivateTemplate::class);
        $templateUuid = ($activateTemplate)(
            $mockedTemplate->getCategoryTreeId(),
            $mockedTemplate->getCode(),
            $mockedTemplate->getLabelCollection()
        );
        $this::assertFalse($this->retrieveTemplateDeactivationStatus($templateUuid));

        $markTemplateAsDeactivated = $this->get(DeactivateTemplate::class);
        $markTemplateAsDeactivated->execute((string) $templateUuid);
        $this::assertTrue($this->retrieveTemplateDeactivationStatus($templateUuid));
    }

    /**
     * With this test we want to make sure that if a template does not exist it will not stop execution.
     * This is to cover the use case of a user trying to deactivate a template already deactivated by another user.
     * Even if no template were found we still inform the user that template deactivation was successful
     */
    public function testItDoesNotCrashIfTemplateDoesNotExists(): void
    {
        $nonExistingTemplateUuid = TemplateUuid::fromString('a1b744e2-a84b-4f74-832f-01aeb202d0ce');
        try {
            $this::assertFalse($this->retrieveTemplateDeactivationStatus($nonExistingTemplateUuid));
        } catch (\Exception $e) {
            $this->fail('An unexpected exception was thrown: '.$e->getMessage());
        }
    }

    private function retrieveTemplateDeactivationStatus(TemplateUuid $templateUuid): ?bool
    {
        $query = <<<SQL
            SELECT is_deactivated 
            FROM pim_catalog_category_template
            WHERE uuid = :template_uuid;
        SQL;

        return (bool) $this->get('database_connection')->executeQuery(
            $query,
            ['template_uuid' => $templateUuid->toBytes()],
            ['template_uuid' => \PDO::PARAM_STR],
        )->fetchOne();

    }
}
