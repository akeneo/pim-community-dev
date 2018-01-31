@javascript
Feature:
  In order to import valid files
  As a product manager
  I need to see that UTF-8 encoding has been checked

  Background:
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"

  Scenario: Import a file that contains non UTF-8 characters
    Given I am on the "csv_footwear_product_import" import job page
    When I upload and import the file "product_export_with_non_utf8_characters.csv"
    And I wait for the "csv_footwear_product_import" job to finish
    Then I should see the text "The file \"/tmp/pim/upload_tmp_dir/product_export_with_non_utf8_characters.csv\" is not correctly encoded in UTF-8. The lines 11, 15 are erroneous."

  Scenario: Import a file that contains only UTF-8 characters
    Given the following CSV file to import:
      """
      sku;name-en_US;description-en_US-ecommerce
      SKU-001;Donec;dictum magna. Ut tincidunt orci quis lectus. Nullam suscipit, est
      """
    And the following job "csv_footwear_product_import" configuration:
    | filePath | %file to import% |
    When I am on the "csv_footwear_product_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_product_import" job to finish
    Then I should see the text "File encoding: UTF-8 OK"

  Scenario: Import a file which content encoding should not be checked
    Given I am on the "csv_footwear_product_import" import job page
    When I upload and import the file "caterpillar_import.zip"
    And I wait for the "csv_footwear_product_import" job to finish
    Then I should see the text "File encoding: skipped, extension in white list"
