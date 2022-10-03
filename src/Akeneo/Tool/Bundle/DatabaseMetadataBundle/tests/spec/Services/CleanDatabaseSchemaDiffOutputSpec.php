<?php

namespace spec\Akeneo\Tool\Bundle\DatabaseMetadataBundle\Services;

use Akeneo\Tool\Bundle\DatabaseMetadataBundle\Services\CleanDatabaseSchemaDiffOutput;
use PhpSpec\ObjectBehavior;
use PHPUnit\Framework\Assert;

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class CleanDatabaseSchemaDiffOutputSpec extends ObjectBehavior
{
    private const LINES = "@@ -24,9 +24,0 @@
-akeneo_onboarder_message | BASE TABLE
-akeneo_onboarder_nice_to_have_attribute | BASE TABLE
-akeneo_onboarder_pre_ref_product | BASE TABLE
-akeneo_onboarder_retailer_contact | BASE TABLE
-akeneo_onboarder_supplier | BASE TABLE
-akeneo_onboarder_supplier_contact | BASE TABLE
-akeneo_onboarder_supplier_family | BASE TABLE
-akeneo_onboarder_supplier_has_catalog_locale | BASE TABLE
-akeneo_onboarder_supplier_user | BASE TABLE
@@ -149,2 +141,0 @@
-supplier | BASE TABLE
-supplier_family | BASE TABLE
@@ -307,32 +303,0 @@
-akeneo_onboarder_message | identifier | NO | varchar(36) | PRI
-akeneo_onboarder_message | created_at | YES | datetime |
-akeneo_onboarder_message | content_field | NO | longtext |
-akeneo_onboarder_message | routingKey_field | NO | varchar(100) |
-akeneo_onboarder_message | error_field | YES | longtext |
-akeneo_onboarder_nice_to_have_attribute | family_id | NO | int | PRI
-akeneo_onboarder_nice_to_have_attribute | attribute_id | NO | int | PRI
-akeneo_onboarder_pre_ref_product | id | NO | varchar(255) | PRI
-akeneo_onboarder_pre_ref_product | supplier_reference | NO | varchar(255) |
-akeneo_onboarder_pre_ref_product | raw_values | YES | json |
-akeneo_onboarder_pre_ref_product | supplier_id | NO | varchar(36) | MUL
-akeneo_onboarder_pre_ref_product | family_id | NO | int |
-akeneo_onboarder_pre_ref_product | raw_categories | YES | json |
-akeneo_onboarder_pre_ref_product | created_at | NO | datetime |
-akeneo_onboarder_pre_ref_product | is_rejected | NO | tinyint(1) |
-akeneo_onboarder_retailer_contact | supplier_id | NO | char(36) | PRI
-akeneo_onboarder_retailer_contact | user_id | NO | int | PRI
-akeneo_onboarder_supplier | id | NO | int | PRI
-akeneo_onboarder_supplier | code | NO | varchar(50) | UNI
-akeneo_onboarder_supplier | sort_order | NO | int |
-akeneo_onboarder_supplier | name | NO | varchar(255) |
-akeneo_onboarder_supplier_contact | supplier_id | NO | varchar(36) | MUL
-akeneo_onboarder_supplier_contact | user_id | NO | varchar(36) | PRI
-akeneo_onboarder_supplier_family | supplier_id | NO | int | PRI
-akeneo_onboarder_supplier_family | family_id | NO | int | PRI
-akeneo_onboarder_supplier_has_catalog_locale | supplier_id | NO | char(36) | PRI
-akeneo_onboarder_supplier_has_catalog_locale | catalog_locale_id | NO | int | PRI
-akeneo_onboarder_supplier_user | identifier_field | NO | varchar(36) | PRI
-akeneo_onboarder_supplier_user | email_field | NO | varchar(255) | UNI
-akeneo_onboarder_supplier_user | firstName_field | NO | varchar(255) |
-akeneo_onboarder_supplier_user | lastName_field | NO | varchar(255) |
-akeneo_onboarder_supplier_user | phone_field | YES | varchar(20) |
@@ -887,9 +854,0 @@
-supplier | id | NO | char(36) | PRI
-supplier | logo_id | YES | int | UNI
-supplier | ui_locale_id | NO | int | MUL
-supplier | catalog_locale_id | NO | int | MUL
-supplier | code | NO | varchar(255) | UNI
-supplier | name | NO | varchar(255) |
-supplier | allow_product_creation | NO | tinyint(1) |
-supplier_family | supplier_id | NO | varchar(36) | PRI
-supplier_family | family_id | NO | int | PRI
@@ -887,9 +854,0 @@
-keep_this_table | keep | NO | int | PRI";

    function it_is_initializable(): void
    {
        $this->beConstructedWith(false);
        $this->shouldHaveType(CleanDatabaseSchemaDiffOutput::class);
    }

    public function it_removes_onboarder_tables_if_the_onboarder_bundle_is_not_activated(): void
    {
        $this->beConstructedWith(false);
        $cleanedLines = $this->__invoke(self::LINES)->getWrappedObject();

        Assert::assertEquals("@@ -887,9 +854,0 @@
-keep_this_table | keep | NO | int | PRI", $cleanedLines);
    }

    public function it_does_not_remove_onboarder_tables_if_bundle_is_activated(): void
    {
        $this->beConstructedWith(true);
        $cleanedLines = $this->__invoke(self::LINES)->getWrappedObject();

        Assert::assertEquals(self::LINES, $cleanedLines);
    }
}
