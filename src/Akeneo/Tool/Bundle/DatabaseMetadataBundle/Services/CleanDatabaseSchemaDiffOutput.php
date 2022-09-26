<?php

namespace Akeneo\Tool\Bundle\DatabaseMetadataBundle\Services;

class CleanDatabaseSchemaDiffOutput
{
    private const ONBOARDER_ELEMENTS = [
        '-akeneo_onboarder_message | BASE TABLE',
        '-akeneo_onboarder_nice_to_have_attribute | BASE TABLE',
        '-akeneo_onboarder_pre_ref_product | BASE TABLE',
        '-akeneo_onboarder_retailer_contact | BASE TABLE',
        '-akeneo_onboarder_supplier | BASE TABLE',
        '-akeneo_onboarder_supplier_contact | BASE TABLE',
        '-akeneo_onboarder_supplier_family | BASE TABLE',
        '-akeneo_onboarder_supplier_has_catalog_locale | BASE TABLE',
        '-akeneo_onboarder_supplier_user | BASE TABLE',
        '-supplier | BASE TABLE',
        '-supplier_family | BASE TABLE',
        '-akeneo_onboarder_message | identifier | NO | varchar(36) | PRI',
        '-akeneo_onboarder_message | created_at | YES | datetime |',
        '-akeneo_onboarder_message | content_field | NO | longtext |',
        '-akeneo_onboarder_message | routingKey_field | NO | varchar(100) |',
        '-akeneo_onboarder_message | error_field | YES | longtext |',
        '-akeneo_onboarder_nice_to_have_attribute | family_id | NO | int | PRI',
        '-akeneo_onboarder_nice_to_have_attribute | attribute_id | NO | int | PRI',
        '-akeneo_onboarder_pre_ref_product | id | NO | varchar(255) | PRI',
        '-akeneo_onboarder_pre_ref_product | supplier_reference | NO | varchar(255) |',
        '-akeneo_onboarder_pre_ref_product | raw_values | YES | json |',
        '-akeneo_onboarder_pre_ref_product | supplier_id | NO | varchar(36) | MUL',
        '-akeneo_onboarder_pre_ref_product | family_id | NO | int |',
        '-akeneo_onboarder_pre_ref_product | raw_categories | YES | json |',
        '-akeneo_onboarder_pre_ref_product | created_at | NO | datetime |',
        '-akeneo_onboarder_pre_ref_product | is_rejected | NO | tinyint(1) |',
        '-akeneo_onboarder_retailer_contact | supplier_id | NO | char(36) | PRI',
        '-akeneo_onboarder_retailer_contact | user_id | NO | int | PRI',
        '-akeneo_onboarder_supplier | id | NO | int | PRI',
        '-akeneo_onboarder_supplier | code | NO | varchar(50) | UNI',
        '-akeneo_onboarder_supplier | sort_order | NO | int |',
        '-akeneo_onboarder_supplier | name | NO | varchar(255) |',
        '-akeneo_onboarder_supplier_contact | supplier_id | NO | varchar(36) | MUL',
        '-akeneo_onboarder_supplier_contact | user_id | NO | varchar(36) | PRI',
        '-akeneo_onboarder_supplier_family | supplier_id | NO | int | PRI',
        '-akeneo_onboarder_supplier_family | family_id | NO | int | PRI',
        '-akeneo_onboarder_supplier_has_catalog_locale | supplier_id | NO | char(36) | PRI',
        '-akeneo_onboarder_supplier_has_catalog_locale | catalog_locale_id | NO | int | PRI',
        '-akeneo_onboarder_supplier_user | identifier_field | NO | varchar(36) | PRI',
        '-akeneo_onboarder_supplier_user | email_field | NO | varchar(255) | UNI',
        '-akeneo_onboarder_supplier_user | firstName_field | NO | varchar(255) |',
        '-akeneo_onboarder_supplier_user | lastName_field | NO | varchar(255) |',
        '-akeneo_onboarder_supplier_user | phone_field | YES | varchar(20) |',
        '-supplier | id | NO | char(36) | PRI',
        '-supplier | logo_id | YES | int | UNI',
        '-supplier | ui_locale_id | NO | int | MUL',
        '-supplier | catalog_locale_id | NO | int | MUL',
        '-supplier | code | NO | varchar(255) | UNI',
        '-supplier | name | NO | varchar(255) |',
        '-supplier | allow_product_creation | NO | tinyint(1) |',
        '-supplier_family | supplier_id | NO | varchar(36) | PRI',
        '-supplier_family | family_id | NO | int | PRI',
    ];

    private bool $isOnboarderEnabled = false;

    public function __construct($isOnboarderEnabled)
    {
        $this->isOnboarderEnabled = boolval($isOnboarderEnabled);
    }

    public function __invoke(string $lines): string
    {
        if (0 == strlen($lines) || $this->isOnboarderEnabled) {
            return $lines;
        }

        $lines = preg_split("/\r\n|\n|\r/", $lines);

        $keySeparator = null;
        foreach ($lines as $key => $line) {
            if (str_contains($line, '@@')) {
                $keySeparator = $key;
            }

            if (in_array($line, static::ONBOARDER_ELEMENTS)) {
                unset($lines[$key]);

                if (null !== $keySeparator) {
                    unset($lines[$keySeparator]);
                    $keySeparator = null;
                }
            }
        }

        return empty($lines) ? '' : implode("\n", $lines);
    }
}
