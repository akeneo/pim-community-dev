Feature: Import categories
  In order to reuse the categories of my products
  As a product manager
  I need to be able to import categories

  Scenario: Successfully import categories in CSV
    Given the "footwear" catalog configuration
    And the following CSV file to import:
      """
      code;parent;label-en_US
      default;;
      computers;;Computers
      laptops;computers;Laptops
      hard_drives;laptops;Hard drives
      pc;computers;PC
      """
    When I import it via the job "csv_footwear_category_import" as "Julia"
    And I wait for this job to finish
    Then there should be the following categories:
      | code        | label       | parent    |
      | computers   | Computers   |           |
      | laptops     | Laptops     | computers |
      | hard_drives | Hard drives | laptops   |
      | pc          | PC          | computers |

  Scenario: Import categories with missing parent
    Given the "footwear" catalog configuration
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
    When I import it via the job "csv_footwear_category_import" as "Julia"
    And I wait for this job to finish
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
    And the following CSV file to import:
      """
      code;parent;label-en_US
      ;;label US
      """
    When I import it via the job "csv_footwear_category_import" as "Julia"
    And I wait for this job to finish
    And I should see the text "Field \"code\" must be filled"

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
    When I import it via the job "xlsx_footwear_category_import" as "Julia"
    And I wait for this job to finish
    Then there should be the following categories:
      | code        | label       | parent    |
      | computers   | Computers   |           |
      | laptops     | Laptops     | computers |
      | hard_drives | Hard drives | laptops   |
      | pc          | PC          | computers |

    Scenario: Import categories with empty labels
      Given the "footwear" catalog configuration
      And the following CSV file to import:
      """
      code;parent;label-en_US
      spring_collection;2014_collection;
      summer_collection;2014_collection;
      """
    When I import it via the job "csv_footwear_category_import" as "Julia"
    And I wait for this job to finish
      And I am on the categories page
      Then I should see the text "[spring_collection]"
      And I should see the text "[summer_collection]"
