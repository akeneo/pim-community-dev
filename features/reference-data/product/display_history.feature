@javascript
Feature: Display the product history
  In order to know by who, when and what changes have been made to a product with reference data
  As a product manager
  I need to have access to a product history

  Background:
    Given a "default" catalog configuration
    And the following products:
      | sku     |
      | sandals |
    And the following attributes:
      | code    | type                        | reference_data_name |
      | color   | reference_data_simpleselect | color               |
      | fabrics | reference_data_multiselect  | fabrics             |
    And the following "color" attribute reference data: Red, Green, Blue
    And the following "fabrics" attribute reference data: Cashmerewool, Neoprene and Silk
    And I am logged in as "Julia"

  Scenario: Add an available "simple select" reference data to a product
    Given I am on the "sandals" product page
    And I add available attribute color
    And I fill in the following information:
      | color | Red |
    Then I save the product
    And I open the history
    And I should see history:
      | version | property | value |
      | 2       | [color]  | Red   |
    When I visit the "Attributes" tab
    And I fill in the following information:
      | color | Green |
    Then I save the product
    And I open the history
    And I should see history in panel:
      | version | property | value |
      | 3       | [color]  | Green |

  Scenario: Add an available "multi select" reference data to a product
    Given I am on the "sandals" product page
    And I add available attribute fabrics
    And I fill in the following information:
      | fabrics | Cashmerewool, Neoprene |
    Then I save the product
    And I open the history
    And I should see history in panel:
      | version | property  | value                 |
      | 2       | [fabrics] | Cashmerewool,Neoprene |
