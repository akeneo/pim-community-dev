@javascript
Feature: Display the completeness of a product with reference data
  In order to see the completeness of a product with reference data in the catalog
  As a product manager
  I need to be able to display the completeness of a product

  Background:
    Given a "footwear" catalog configuration
    And I add the "french" locale to the "tablet" channel
    And I add the "french" locale to the "mobile" channel
    And the following attributes:
      | code        | label-en_US | localizable | scopable | type                            | reference_data_name | group |
      | heel_fabric | Heel fabric | 1           | 1        | pim_reference_data_multiselect  | fabrics             | other |
      | main_fabric | Main fabric | 0           | 1        | pim_reference_data_multiselect  | fabrics             | other |
      | main_color  | Main color  | 1           | 0        | pim_reference_data_simpleselect | color               | other |
    And the following family:
      | code      | attributes                                                    | requirements-tablet                   | requirements-mobile                    |
      | highheels | sku,heel_color,sole_fabric,heel_fabric,main_fabric,main_color | sku,heel_color,sole_fabric,main_color | sku,heel_fabric,main_fabric,main_color |
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
    When I visit the "Completeness" column tab
    Then I should see the completeness:
      | channel | locale | state   | ratio |
      | tablet  | en_US  | warning | 50%   |
      | tablet  | fr_FR  | warning | 75%   |
      | mobile  | en_US  | warning | 50%   |
      | mobile  | fr_FR  | success | 100%  |
    When I am on the "black-heels" product page
    And I visit the "Completeness" column tab
    Then I should see the completeness:
      | channel | locale | state   | ratio |
      | tablet  | en_US  | success | 100%  |
      | tablet  | fr_FR  | warning | 75%   |
      | mobile  | en_US  | warning | 50%   |
      | mobile  | fr_FR  | warning | 25%   |
    When I am on the "green-heels" product page
    And I visit the "Completeness" column tab
    Then I should see the completeness:
      | channel | locale | state   | ratio |
      | tablet  | en_US  | success | 100%  |
      | tablet  | fr_FR  | success | 100%  |
      | mobile  | en_US  | warning | 50%   |
      | mobile  | fr_FR  | warning | 50%   |
    When I am on the "high-heels" product page
    And I visit the "Completeness" column tab
    Then I should see the completeness:
      | channel | locale | state   | ratio |
      | tablet  | en_US  | warning | 25%   |
      | tablet  | fr_FR  | warning | 25%   |
      | mobile  | en_US  | warning | 25%   |
      | mobile  | fr_FR  | warning | 25%   |
