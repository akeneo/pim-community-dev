@javascript
Feature: Handle import of invalid data
  In order to ease the correction of an invalid file import
  As a product manager
  I need to be able to download a file containing all invalid data of an import

  Background:
    Given the "footwear" catalog configuration

  Scenario: From a CSV family import, create an invalid data file and be able to download it
    Given the following CSV file to import:
      """
      code;attributes
      a_family_1;name,description,color
      a_family_2;name,description,number_in_stock
      a_family_3;name,description,size
      a_family_4;name,description,top_view
      a_family_5;name,description,WATERPROOF
      a_family_6;name,description,heel_color
      a_family_7;name,description,weight
      a_family_8;name,description,BULLETPROOF
      a_family_9;name,description,destocking_date
      """
    And the following job "csv_footwear_family_import" configuration:
      | filePath | %file to import% |
    And I am logged in as "Julia"
    And I launch the "csv_footwear_family_import" import job
    And I wait for the "csv_footwear_family_import" job to finish
    Then I should see the text "Download invalid data"
    And the invalid data file of "csv_footwear_family_import" should contain:
      """
      code;attributes
      a_family_5;name,description,WATERPROOF
      a_family_8;name,description,BULLETPROOF
      """

  Scenario: From a CSV product import, create an invalid data file and be able to download it
    Given the following CSV file to import:
      """
      sku;family;groups;categories;name-en_US;description-en_US-tablet
      SKU-001;NO_FAMILY;CROSS;winter_boots;Donec;dictum magna. Ut tincidunt orci quis lectus. Nullam suscipit, est
      SKU-002;sneakers;;winter_boots;Donex;Pellentesque habitant morbi tristique senectus et netus et malesuada fames
      SKU-003;sneakers;;sandals;ac;Morbi quis urna. Nunc quis arcu vel quam dignissim pharetra.
      SKU-004;sneakers;;sandals;nec;justo sit amet nulla. Donec non justo. Proin non massa
      SKU-005;boots;CROSS;winter_boots,sandals;non;tincidunt dui augue eu tellus. Phasellus elit pede, malesuada vel
      SKU-006;boots;CROSS;winter_boots;ipsum;Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aliquam auctor,
      SKU-007;sneakers;;;rutrum.;quis, pede. Praesent eu dui. Cum sociis natoque penatibus et
      SKU-008;OTHER_FAMILY;CROSS;winter_boots;ligula;urna et arcu imperdiet ullamcorper. Duis at lacus. Quisque purus
      SKU-009;sneakers;;;porttitor;sagittis. Duis gravida. Praesent eu nulla at sem molestie sodales.
      SKU-010;boots;CROSS;sandals;non,;vestibulum nec, euismod in, dolor. Fusce feugiat. Lorem ipsum dolor
      """
    And the following job "csv_footwear_product_import" configuration:
      | filePath | %file to import% |
    And I am logged in as "Julia"
    And I launch the "csv_footwear_product_import" import job
    And I wait for the "csv_footwear_product_import" job to finish
    Then I should see the text "Download invalid data"
    And the invalid data file of "csv_footwear_product_import" should contain:
      """
      code;attributes
      SKU-001;NO_FAMILY;CROSS;winter_boots;Donec;dictum magna. Ut tincidunt orci quis lectus. Nullam suscipit, est
      SKU-008;OTHER_FAMILY;CROSS;winter_boots;ligula;urna et arcu imperdiet ullamcorper. Duis at lacus. Quisque purus
      """

  Scenario: From a XLSX product import, create an invalid data file and be able to download it
    Given the following XLSX file to import:
      """
      sku;family
      SKU-001;NO_FAMILY
      SKU-002;sneakers
      SKU-003;sneakers
      SKU-004;sneakers
      SKU-005;boots
      SKU-006;boots
      SKU-007;sneakers
      SKU-008;OTHER_FAMILY
      SKU-009;sneakers
      SKU-010;boots
      """
    And the following job "xlsx_footwear_product_import" configuration:
      | filePath | %file to import% |
    And I am logged in as "Julia"
    And I launch the "xlsx_footwear_product_import" import job
    And I wait for the "xlsx_footwear_product_import" job to finish
    Then I should see the text "Download invalid data"
    And the invalid data file of "xlsx_footwear_product_import" should contain:
      """
      sku;family
      SKU-001;NO_FAMILY
      SKU-008;OTHER_FAMILY
      """
