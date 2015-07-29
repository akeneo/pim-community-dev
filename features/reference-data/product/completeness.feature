@javascript
Feature: Display the completeness of a product
  In order to see the completeness of a product in the catalog
  As a product manager
  I need to be able to display the completeness of a product

  Background:
    Given a "footwear" catalog configuration
    And I add the "french" locale to the "tablet" channel
    And I add the "french" locale to the "mobile" channel
    And the following attributes:
      | code        | label       | localizable | scopable | type                        | reference_data_name |
      | heel_fabric | Heel fabric | yes         | yes      | reference_data_multiselect  | fabrics             |
      | main_fabric | Main fabric | no          | yes      | reference_data_multiselect  | fabrics             |
      | main_color  | Main color  | yes         | no       | reference_data_simpleselect | color               |
    And the following family:
      | code      | attributes                                                         | requirements-tablet                      | requirements-mobile                      |
      | highheels | sku, heel_color, sole_fabric, heel_fabric, main_fabric, main_color | sku, heel_color, sole_fabric, main_color | sku,heel_fabric, main_fabric, main_color |
    And I am logged in as "Julia"
    And the following "main_fabric" attribute reference data: PVC, Nylon, Neoprene, Spandex, Wool, Kevlar, Jute
    And the following "main_color" attribute reference data: Red, Green, Light green, Blue, Yellow, Cyan, Magenta, Black, White
    And the following products:
      | sku         | family    | heel_color | sole_fabric | heel_fabric-en_US-mobile | heel_fabric-fr_FR-mobile | heel_fabric-en_US-tablet | heel_fabric-fr_FR-tablet | main_fabric-mobile | main_fabric-tablet | main_color-fr_FR | main_color-en_US |
      | red-heels   | highheels |            | Spandex     |                          | Neoprene,Jute            |                          |                          | PVC                |                    | Red              |                  |
      | black-heels | highheels | Black      | Wool        |                          |                          | Nylon                    | Kevlar,Jute              |                    | Nylon              |                  | Black            |
      | green-heels | highheels | Green      | PVC         |                          |                          |                          |                          |                    |                    | Green            | Light green      |
      | high-heels  | highheels |            |             |                          |                          |                          |                          |                    |                    |                  |                  |
    And I launched the completeness calculator

  Scenario: Successfully display the completeness of the products with reference data
    Given I am on the "red-heels" product page
    When I open the "Completeness" panel
    Then I should see the completeness summary
    And I should see the completeness:
      | channel | locale | state   | message          | ratio |
      | mobile  | en_US  | warning | 2 missing values | 50%   |
      | mobile  | fr_FR  | success | Complete         | 100%  |
      | tablet  | en_US  | warning | 2 missing values | 50%   |
      | tablet  | fr_FR  | warning | 1 missing value  | 75%   |
    When I am on the "black-heels" product page
    And I open the "Completeness" panel
    Then I should see the completeness summary
    And I should see the completeness:
      | channel | locale | state   | message          | ratio |
      | mobile  | en_US  | warning | 2 missing values | 50%   |
      | mobile  | fr_FR  | warning | 3 missing values | 25%   |
      | tablet  | en_US  | success | Complete         | 100%  |
      | tablet  | fr_FR  | warning | 1 missing value  | 75%   |
    When I am on the "green-heels" product page
    And I open the "Completeness" panel
    Then I should see the completeness summary
    And I should see the completeness:
      | channel | locale | state   | message          | ratio |
      | mobile  | en_US  | warning | 2 missing values | 50%   |
      | mobile  | fr_FR  | warning | 2 missing values | 50%   |
      | tablet  | en_US  | success | Complete         | 100%  |
      | tablet  | fr_FR  | success | Complete         | 100%  |
    When I am on the "high-heels" product page
    And I open the "Completeness" panel
    Then I should see the completeness summary
    And I should see the completeness:
      | channel | locale | state   | message          | ratio |
      | mobile  | en_US  | warning | 3 missing values | 25%   |
      | mobile  | fr_FR  | warning | 3 missing values | 25%   |
      | tablet  | en_US  | warning | 3 missing values | 25%   |
      | tablet  | fr_FR  | warning | 3 missing values | 25%   |

  Scenario: Successfully display the completeness of the products with reference data in the grid
    Given I am on the products page
    And I switch the locale to "English (United States)"
    And I filter by "Channel" with value "Mobile"
    Then the row "red-heels" should contain:
      | column   | value |
      | complete | 50%   |
    Then the row "black-heels" should contain:
      | column   | value |
      | complete | 50%   |
    Then the row "green-heels" should contain:
      | column   | value |
      | complete | 50%   |
    Then the row "high-heels" should contain:
      | column   | value |
      | complete | 25%   |
    And I filter by "Channel" with value "Tablet"
    Then the row "red-heels" should contain:
      | column   | value |
      | complete | 50%   |
    Then the row "black-heels" should contain:
      | column   | value |
      | complete | 100%  |
    Then the row "green-heels" should contain:
      | column   | value |
      | complete | 100%  |
    Then the row "high-heels" should contain:
      | column   | value |
      | complete | 25%   |
    And I switch the locale to "French (France)"
    And I filter by "Channel" with value "Mobile"
    Then the row "red-heels" should contain:
      | column   | value |
      | complete | 100%  |
    Then the row "black-heels" should contain:
      | column   | value |
      | complete | 25%   |
    Then the row "green-heels" should contain:
      | column   | value |
      | complete | 50%   |
    Then the row "high-heels" should contain:
      | column   | value |
      | complete | 25%   |
    And I filter by "Channel" with value "Tablet"
    Then the row "red-heels" should contain:
      | column   | value |
      | complete | 75%   |
    Then the row "black-heels" should contain:
      | column   | value |
      | complete | 75%   |
    Then the row "green-heels" should contain:
      | column   | value |
      | complete | 100%  |
    Then the row "high-heels" should contain:
      | column   | value |
      | complete | 25%   |

