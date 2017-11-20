@javascript
Feature: Revert a product to a previous version
  In order to manage versioning products
  As a product manager
  I need to be able to revert a product to a previous version

  Background:
    Given a "footwear" catalog configuration
    And the following attributes:
      | code        | label-en_US | type                            | reference_data_name | group |
      | main_fabric | Main fabric | pim_reference_data_multiselect  | fabrics             | other |
      | main_color  | Main color  | pim_reference_data_simpleselect | color               | other |
    And the following reference data:
      | type   | code         | label        |
      | color  | red          |              |
      | color  | blue         |              |
      | color  | green        | Green        |
      | fabric | cashmerewool | Cashmerewool |
      | fabric | neoprene     |              |
      | fabric | silk         | Silk         |
    And the following family:
      | code       | attributes                            |
      | high_heels | sku,name,main_color,main_fabric,color |
    And I am logged in as "Julia"
    And I am on the products grid
    And I create a product
    And I fill in the following information in the popin:
      | SKU    | red-heels  |
      | family | high_heels |
    And I press the "Save" button in the popin
    And I wait to be on the "red-heels" product page
    And I visit the "Other" group
    And I fill in the following information:
      | Main color  | [red]                        |
      | Main fabric | Cashmerewool, neoprene, Silk |
    And I press the "Save" button
    Then I should not see the text "There are unsaved changes."

  Scenario: Revert a product with simple reference data
    Given I am on the "red-heels" product page
    And I visit the "Other" group
    And I fill in the following information:
      | Main color | Green |
    Then I save the product
    And I should not see the text "There are unsaved changes."
    When I visit the "History" column tab
    Then I should see history:
      | version | property   | value |
      | 3       | Main color | green |
    When I visit the "Attributes" column tab
    And I visit the "Other" group
    And I fill in the following information:
      | Main color | [blue] |
    And I save the product
    Then I should not see the text "There are unsaved changes."
    When I visit the "History" column tab
    Then I should see history:
      | version | property   | value |
      | 4       | Main color | blue  |
    When I revert the product version number 2 and then see 5 total versions
    Then the product "red-heels" should have the following values:
      | main_color | [red] |

  Scenario: Revert a product with multiple reference data
    Given I am on the "red-heels" product page
    And I visit the "Other" group
    And I fill in the following information:
      | Main fabric | Cashmerewool, neoprene |
    And I save the product
    Then I should not see the text "There are unsaved changes."
    When I visit the "History" column tab
    And I should see history:
      | version | property    | value                 |
      | 3       | Main fabric | cashmerewool,neoprene |
    When I revert the product version number 2
    Then the product "red-heels" should have the following values:
      | main_fabric | Cashmerewool, [neoprene], Silk |
