@javascript
Feature: Import categories
  In order to reuse the categories of my products
  As a product manager
  I need to be able to import categories

  Scenario: Succesfully import categories
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    And the following file to import:
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
    And the following file to import:
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
