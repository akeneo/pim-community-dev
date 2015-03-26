@javascript
Feature: Revert a product with reference data to a previous version
  In order to manage versioning products
  As a product manager
  I need to be able to revert a product to a previous version

  Background:
    Given a "footwear" catalog configuration
    And the following attributes:
      | code        | label       | type                        | property-reference_data_name |
      | main_fabric | Main fabric | reference_data_multiselect  | fabrics                        |
      | main_color  | Main color  | reference_data_simpleselect | color                          |
    And I am logged in as "Julia"
    And the following "main_fabric" attribute reference data: PVC, Nylon, Neoprene, Spandex, Wool, Kevlar, Jute
    And the following "main_color" attribute reference data: Red, Green, Light green, Blue, Yellow, Cyan, Magenta, Black, White
    And the following products:
      | sku             | main_color | main_fabric           |
      | red-heels       | Red        | Neoprene,Spandex,Wool |
    And I am logged in as "Julia"

  Scenario: Revert a product with simple reference data
    Given I am on the "red-heels" product page
    And I add available attribute color
    And I visit the "Other" group
    And I fill in the following information:
      | Main color | Green |
    Then I save the product
    And I visit the "History" tab
    And I should see history:
      | version | property   | value |
      | 2       | main_color | Green |
    When I visit the "Attributes" tab
    And I visit the "Other" group
    And I fill in the following information:
      | Main color | Cyan |
    Then I save the product
    And I visit the "History" tab
    And I should see history:
      | version | property   | value |
      | 3       | main_color | Cyan  |
    When I click on the "Revert to this version" action of the row which contains "sku: red-heels"
    Then the product "red-heels" should have the following values:
      | main_color | Red |

  Scenario: Revert a product with multiple reference data
    Given I am on the "red-heels" product page
    And I add available attribute color
    And I visit the "Other" group
    And I fill in the following information:
      | Main fabric | Neoprene,Wool |
    Then I save the product
    And I visit the "History" tab
    And I should see history:
      | version | property    | value         |
      | 2       | main_fabric | Neoprene,Wool |
    When I click on the "Revert to this version" action of the row which contains "sku: red-heels"
    Then the product "red-heels" should have the following values:
      | main_fabric | Neoprene, Spandex, Wool |
