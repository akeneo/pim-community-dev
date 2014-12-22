@javascript
Feature: Import categories
  In order to reuse the categories of my products
  As a product manager
  I need to be able to import categories

  Scenario: Successfully import categories
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
    And the following job "footwear_category_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_category_import" import job page
    And I launch the import job
    And I wait for the "footwear_category_import" job to finish
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
    And the following job "footwear_category_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_category_import" import job page
    And I launch the import job
    And I wait for the "footwear_category_import" job to finish
    Then I should see "parent: No category with code clothes"
    And I should see "parent: No category with code tshirts"
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
    And the following job "footwear_category_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_category_import" import job page
    And I launch the import job
    And I wait for the "footwear_category_import" job to finish
    Then I should see "skipped 1"
    And I should see "code: This value should not be blank"
