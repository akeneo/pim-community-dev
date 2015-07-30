@javascript
Feature: Revert a product to a previous version
  In order to manage versioning products
  As a product manager
  I need to be able to revert a product to a previous version

  Background:
    Given a "footwear" catalog configuration
    And the following attributes:
      | code        | label       | type                        | reference_data_name |
      | main_fabric | Main fabric | reference_data_multiselect  | fabrics             |
      | main_color  | Main color  | reference_data_simpleselect | color               |
    And the following reference data:
      | type   | code         | label        |
      | color  | red          |              |
      | color  | blue         |              |
      | color  | green        | Green        |
      | fabric | cashmerewool | Cashmerewool |
      | fabric | neoprene     |              |
      | fabric | silk         | Silk         |
    And the following products:
      | sku       | main_color | main_fabric                |
      | red-heels | red        | cashmerewool,neoprene,silk |
    And I am logged in as "Julia"

  Scenario: Revert a product with simple reference data
    Given I am on the "red-heels" product page
    And I add available attribute color
    And I visit the "Other" group
    And I fill in the following information:
      | Main color | Green |
    Then I save the product
    And I open the history
    And I should see history:
      | version | property   | value |
      | 2       | Main color | green |
    When I visit the "Attributes" tab
    And I visit the "Other" group
    And I fill in the following information:
      | Main color | [blue] |
    Then I save the product
    And I open the history
    And I should see history:
      | version | property   | value |
      | 3       | Main color | blue  |
    When I revert the product version number 1
    Then the product "red-heels" should have the following values:
      | main_color | [red] |

  Scenario: Revert a product with multiple reference data
    Given I am on the "red-heels" product page
    And I add available attribute color
    And I visit the "Other" group
    And I fill in the following information:
      | Main fabric | Cashmerewool, [neoprene] |
    Then I save the product
    And I open the history
    And I should see history:
      | version | property    | value                 |
      | 2       | Main fabric | cashmerewool,neoprene |
    When I revert the product version number 1
    Then the product "red-heels" should have the following values:
      | main_fabric | Cashmerewool, [neoprene], Silk |
