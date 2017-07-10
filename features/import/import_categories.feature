@javascript
Feature: Import categories
  In order to reuse the categories of my products
  As a product manager
  I need to be able to import categories

  Scenario: Successfully import categories in CSV
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    And the following CSV file to import:
      """
      code;parent;label-en_US
      default;;
      computers;;Computers
      laptops;computers;Laptops
      hard_drives;laptops;Hard drives
      pc;computers;PC
      """
    And the following job "csv_footwear_category_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_category_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_category_import" job to finish
    Then there should be the following categories:
      | code        | label       | parent    |
      | computers   | Computers   |           |
      | laptops     | Laptops     | computers |
      | hard_drives | Hard drives | laptops   |
      | pc          | PC          | computers |

  Scenario: Import categories with missing parent
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

  @jira https://akeneo.atlassian.net/browse/PIM-3311
  Scenario: Skip categories with empty code
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    And the following CSV file to import:
      """
      code;parent;label-en_US
      ;;label US
      """
    And the following job "csv_footwear_category_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_category_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_category_import" job to finish
    And I should see the text "Field \"code\" must be filled"

  Scenario: Successfully import categories in XLSX
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    And the following XLSX file to import:
      """
      code;parent;label-en_US
      default;;
      computers;;Computers
      laptops;computers;Laptops
      hard_drives;laptops;Hard drives
      pc;computers;PC
      """
    And the following job "xlsx_footwear_category_import" configuration:
      | filePath | %file to import% |
    When I am on the "xlsx_footwear_category_import" import job page
    And I launch the import job
    And I wait for the "xlsx_footwear_category_import" job to finish
    Then there should be the following categories:
      | code        | label       | parent    |
      | computers   | Computers   |           |
      | laptops     | Laptops     | computers |
      | hard_drives | Hard drives | laptops   |
      | pc          | PC          | computers |

    Scenario: Import categories with empty labels
      Given the "footwear" catalog configuration
      And I am logged in as "Julia"
      And the following CSV file to import:
      """
      code;parent;label-en_US
      spring_collection;2014_collection;
      summer_collection;2014_collection;
      """
      And the following job "csv_footwear_category_import" configuration:
        | filePath | %file to import% |
      When I am on the "csv_footwear_category_import" import job page
      And I launch the import job
      And I wait for the "csv_footwear_category_import" job to finish
      And I am on the categories page
      Then I should see the text "[spring_collection]"
      And I should see the text "[summer_collection]"
