@javascript
Feature: Display the completeness of a product with simple or multi selects
  In order to see the completeness of a product with simple or multi selects in the catalog
  As a product manager
  I need to be able to display the completeness of a product

  Background:
    Given a "footwear" catalog configuration
    And I add the "french" locale to the "tablet" channel
    And I add the "french" locale to the "mobile" channel
    And the following attributes:
      | code         | label-en_US  | localizable | scopable | type                     | group |
      | braid_color  | Braid color  | 0           | 0        | pim_catalog_simpleselect | other |
      | braid_fabric | Braid fabric | 0           | 0        | pim_catalog_multiselect  | other |
      | heel_fabric  | Heel fabric  | 1           | 1        | pim_catalog_multiselect  | other |
      | main_fabric  | Main fabric  | 0           | 1        | pim_catalog_multiselect  | other |
      | main_color   | Main color   | 1           | 0        | pim_catalog_simpleselect | other |
    And the following family:
      | code      | attributes                                                      | requirements-tablet                     | requirements-mobile                    |
      | highheels | sku,braid_color,braid_fabric,heel_fabric,main_fabric,main_color | sku,braid_color,braid_fabric,main_color | sku,heel_fabric,main_fabric,main_color |
    And I am logged in as "Julia"
    And the following "braid_fabric" attribute options: PVC, Nylon, Neoprene, Spandex, Wool, Kevlar, Jute
    And the following "braid_color" attribute options: Red, Green, Emerald, Blue, Yellow, Cyan, Magenta, Black, White
    And the following "main_fabric" attribute options: PVC, Nylon, Neoprene, Spandex, Wool, Kevlar, Jute
    And the following "main_color" attribute options: Red, Green, Emerald, Blue, Yellow, Cyan, Magenta, Black, White
    And the following "heel_fabric" attribute options: PVC, Nylon, Neoprene, Spandex, Wool, Kevlar, Jute
    And the following products:
      | sku         | family    | braid_color | braid_fabric | heel_fabric-en_US-mobile | heel_fabric-fr_FR-mobile | heel_fabric-en_US-tablet | heel_fabric-fr_FR-tablet | main_fabric-mobile | main_fabric-tablet | main_color-fr_FR | main_color-en_US |
      | red-heels   | highheels |             | Spandex      |                          | Neoprene,Jute            |                          |                          | PVC                |                    | Red              |                  |
      | black-heels | highheels | Black       | Wool         |                          |                          | Nylon                    | Kevlar,Jute              |                    | Nylon              |                  | Black            |
      | green-heels | highheels | Green       | PVC          |                          |                          |                          |                          |                    |                    | Green            | Emerald          |
      | high-heels  | highheels |             |              |                          |                          |                          |                          |                    |                    |                  |                  |
    And I launched the completeness calculator

  Scenario: Successfully display the completeness of the products with reference data
    Given I am on the "red-heels" product page
    When I open the "Completeness" panel
    Then I should see the completeness:
      | channel | locale | state   | ratio |
      | mobile  | en_US  | warning | 50%   |
      | tablet  | en_US  | warning | 50%   |
      | mobile  | fr_FR  | success | 100%  |
      | tablet  | fr_FR  | warning | 75%   |
    When I am on the "black-heels" product page
    And I open the "Completeness" panel
    Then I should see the completeness:
      | channel | locale | state   | ratio |
      | mobile  | en_US  | warning | 50%   |
      | tablet  | en_US  | success | 100%  |
      | mobile  | fr_FR  | warning | 25%   |
      | tablet  | fr_FR  | warning | 75%   |
    When I am on the "green-heels" product page
    And I open the "Completeness" panel
    Then I should see the completeness:
      | channel | locale | state   | ratio |
      | mobile  | en_US  | warning | 50%   |
      | tablet  | en_US  | success | 100%  |
      | mobile  | fr_FR  | warning | 50%   |
      | tablet  | fr_FR  | success | 100%  |
    When I am on the "high-heels" product page
    And I open the "Completeness" panel
    Then I should see the completeness:
      | channel | locale | state   | ratio |
      | mobile  | en_US  | warning | 25%   |
      | tablet  | en_US  | warning | 25%   |
      | mobile  | fr_FR  | warning | 25%   |
      | tablet  | fr_FR  | warning | 25%   |

  Scenario: Successfully display the completeness of the products with reference data in the grid
    Given I am on the products page
    And I switch the locale to "en_US"
    And I filter by "scope" with operator "equals" and value "Mobil"
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
    And I filter by "scope" with operator "equals" and value "Tablet"
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
    And I switch the locale to "fr_FR"
    And I filter by "scope" with operator "equals" and value "Mobil"
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
    And I filter by "scope" with operator "equals" and value "Tablet"
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

