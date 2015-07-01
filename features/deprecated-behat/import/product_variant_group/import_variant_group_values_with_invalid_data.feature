@deprecated @javascript
Feature: Execute an import
  In order to update existing product information
  As a product manager
  I need to be be notified when I use invalid values in variant group

  # what's tested here ?
  # -----------------------------|---------------|-------------
  # TYPE                         | INVALID VALUE | NULL VALUE
  # -----------------------------|---------------|-------------
  # pim_catalog_boolean          | TODO          | TODO
  # pim_catalog_date             | TODO          | TODO
  # pim_catalog_file             | TODO          | TODO
  # pim_catalog_identifier       | N/A           | N/A
  # pim_catalog_image            | TODO          | TODO
  # pim_catalog_metric           | TODO          | TODO
  # pim_catalog_multiselect      | TODO          | TODO
  # pim_catalog_number           | TODO          | TODO
  # pim_catalog_price_collection | TODO          | TODO
  # pim_catalog_simpleselect     | TODO          | TODO
  # pim_catalog_text             | DONE          | TODO
  # pim_catalog_textarea         | TODO          | TODO

  Background:
    Given the "footwear" catalog configuration
    And the following products:
      | sku              | family  | categories        | size | color |
      | sandal-white-37  | sandals | winter_collection | 37   | white |
      | sandal-white-38  | sandals | winter_collection | 38   | white |
      | sandal-white-39  | sandals | winter_collection | 39   | white |
      | sandal-red-37    | sandals | winter_collection | 37   | red   |
      | sandal-red-38    | sandals | winter_collection | 38   | red   |
      | sandal-red-39    | sandals | winter_collection | 39   | red   |
      | sandal2-white-37 | sandals | winter_collection | 37   | white |
      | sandal2-white-38 | sandals | winter_collection | 38   | white |
      | sandal2-red-37   | sandals | winter_collection | 37   | red   |
      | sandal2-red-38   | sandals | winter_collection | 38   | red   |
    And the following product groups:
      | code    | label     | axis        | type    | products                                                                                       |
      | SANDAL  | Sandal    | size, color | VARIANT | sandal-white-37, sandal-white-38, sandal-white-39, sandal-red-37, sandal-red-38, sandal-red-39 |
      | SANDAL2 | SandalTwo | size, color | VARIANT | sandal2-white-37, sandal2-white-38, sandal2-red-37, sandal2-red-38                             |
    And the following attributes:
      | code        | label-en_US | type | scopable | max_characters | validation_rule | validation_regexp |
      | custom_desc | Desc        | text | no       |                |                 |                   |
      | title       | Title       | text | no       | 22             |                 |                   |
      | barcode     | Barcode     | text | no       |                | regexp          | /^0\d*$/          |
      | link        | Link        | text | no       |                | url             |                   |
    And I am logged in as "Julia"

  Scenario: Skip variant group when a text value is too long (default max_characters to 255)
    Given the following CSV file to import:
      """
      code;type;custom_desc
      SANDAL;VARIANT;"My custom desc is soooo long, soooo long, soooo long, soooo long, soooo long, soooo long, soooo long, soooo long, soooo long, soooo long, soooo long, soooo long, soooo long, soooo long, soooo long, soooo long, soooo long, soooo long, soooo long, soooo long, soooo long, soooo long"
      """
    And the following job "footwear_variant_group_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_variant_group_import" import job page
    And I launch the import job
    And I wait for the "footwear_variant_group_import" job to finish
    Then I should see "This value is too long. It should have 255 characters or less.: My custom desc is soooo long, soooo long, soooo long, soooo long, soooo long, soooo long, soooo long, soooo long, soooo long, soooo long, soooo long, soooo long, soooo long, soooo long, soooo long, soooo long, soooo long, soooo long, soooo long, soooo long, soooo long, soooo long"
    And I should see "Skipped 1"

  Scenario: Skip variant group when a text value is too long (max_characters to 22)
    Given the following CSV file to import:
      """
      code;type;title
      SANDAL;VARIANT;"My title is soooo long, soooo long, soooo long."
      """
    And the following job "footwear_variant_group_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_variant_group_import" import job page
    And I launch the import job
    And I wait for the "footwear_variant_group_import" job to finish
    Then I should see "This value is too long. It should have 22 characters or less.: My title is soooo long, soooo long, soooo long."
    And I should see "Skipped 1"

  Scenario: Skip variant group when a text value does not match the expected regex
    Given the following CSV file to import:
      """
      code;type;barcode
      SANDAL;VARIANT;"ThisIsNotABarcode"
      """
    And the following job "footwear_variant_group_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_variant_group_import" import job page
    And I launch the import job
    And I wait for the "footwear_variant_group_import" job to finish
    Then I should see "This value is not valid.: ThisIsNotABarcode"
    And I should see "Skipped 1"

  Scenario: Skip variant group when a text value is not an url as expected
    Given the following CSV file to import:
      """
      code;type;link
      SANDAL;VARIANT;"ThisIsNotAnUrl"
      """
    And the following job "footwear_variant_group_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_variant_group_import" import job page
    And I launch the import job
    And I wait for the "footwear_variant_group_import" job to finish
    Then I should see "This value is not a valid URL.: ThisIsNotAnUrl"
    And I should see "Skipped 1"

  Scenario: Fail to add unique attributes to variant group during import
    Given the following attributes:
      | code               | unique |
      | unique_description | yes    |
      | unique_label       | yes    |
    And the following CSV file to import:
      """
      code;type;unique_description;unique_label
      caterpillar_boots;VARIANT;foo;bar
      """
    And the following job "footwear_variant_group_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_variant_group_import" import job page
    And I launch the import job
    And I wait for the "footwear_variant_group_import" job to finish
    Then I should see:
    """
    Variant group "caterpillar_boots" cannot contain values for axis or unique attributes: "unique_description", "unique_label"
    """
    And I should see "Skipped 1"
