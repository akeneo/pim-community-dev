Feature: Import categories
  In order to reuse the categories of my products
  As a product manager
  I need to be able to import categories

  @critical @javascript
  Scenario: Successfully import categories in CSV and skipped lines with no parent
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    And the following CSV file to import:
      """
      code;parent;label-en_US
      default;;
      computers;;Computers
      laptops;computers;Laptops
      hard_drives;laptops;Hard drives
      tshirts;clothes;T-shirts
      printed_tshirts;tshirts;Printed T-shirts
      pc;computers;PC
      """
    And the following job "csv_footwear_category_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_category_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_category_import" job to finish
    Then I should see the text "Property \"parent\" expects a valid category code. The category does not exist, \"clothes\" given."
    And I should see the text "Property \"parent\" expects a valid category code. The category does not exist, \"tshirts\" given."
    And there should be the following categories:
      | code        | label       | parent    |
      | computers   | Computers   |           |
      | laptops     | Laptops     | computers |
      | hard_drives | Hard drives | laptops   |
      | pc          | PC          | computers |
    # 5 from the catalog + 5 from this test
    And there should be 10 categories

  Scenario: Successfully import categories in XLSX
    Given the "footwear" catalog configuration
    And the following XLSX file to import:
      """
      code;parent;label-en_US
      default;;
      computers;;Computers
      laptops;computers;Laptops
      hard_drives;laptops;Hard drives
      pc;computers;PC
      """
    When the categories are imported via the job xlsx_footwear_category_import
    Then there should be the following categories:
      | code        | label       | parent    |
      | computers   | Computers   |           |
      | laptops     | Laptops     | computers |
      | hard_drives | Hard drives | laptops   |
      | pc          | PC          | computers |
