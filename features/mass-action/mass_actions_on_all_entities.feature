@javascript
Feature: Apply a mass action on all entities
  In order to modify all items
  As a product manager
  I need to be able to select all entities in the grid and apply mass-edit on them

  Background:
    Given a "footwear" catalog configuration
    And the following products:
      | sku         | family  | name-en_US   | categories   | price          | size | color |
      | super_boots | boots   | Super boots  | winter_boots | 10 USD, 15 EUR | 35   | blue  |
      | mega_boots  | boots   | Mega boots   | winter_boots | 10 USD, 15 EUR | 46   | red   |
      | ultra_boots | boots   | Ultra boots  | winter_boots |                | 36   | black |
      | sandals     | sandals | Tiny sandals | sandals      | 10 USD, 15 EUR | 42   | red   |
    And I am logged in as "Julia"

  Scenario: Edit common attributes of all products
    When I am on the products page
    And I select all products
    And I press mass-edit button
    And I choose the "Edit common attributes" operation
    And I display the Name attribute
    And I change the "Name" to "Same product"
    And I move on to the next step
    And I wait for the "edit-common-attributes" mass-edit job to finish
    Then the product "super_boots" should have the following values:
      | name-en_US | Same product |
    And the product "ultra_boots" should have the following values:
      | name-en_US | Same product |
    And the product "sandals" should have the following values:
      | name-en_US | Same product |

  Scenario: Edit family of all products, filtered by category and completeness
    Given I launched the completeness calculator
    When I am on the products page
    And I filter by "category" with value "2014_collection"
    And I filter by "category" with value "winter_collection"
    And I filter by "Channel" with value "Mobile"
    And I filter by "Complete" with value "yes"
    When I select all products
    And I press mass-edit button
    And I choose the "Change the family of products" operation
    And I change the Family to "Sandals"
    And I move on to the next step
    And I wait for the "change-family" mass-edit job to finish
    Then the family of product "super_boots" should be "sandals"
    Then the family of product "mega_boots" should be "sandals"
    And the family of product "ultra_boots" should be "boots"

  Scenario: Edit all families, filtered by name
    Given the following families:
      | code       | label-en_US     |
      | 4_blocks   | Lego 4 blocks   |
      | 2_blocks   | Lego 2 blocks   |
      | characters | Lego characters |
    When I am on the families page
    And I filter by "Label" with value "contains blocks"
    And I select all products
    And I press mass-edit button
    And I choose the "Set attribute requirements" operation
    And I display the Length attribute
    And I switch the attribute "Length" requirement in channel "Mobile"
    And I move on to the next step
    And I wait for the "set-attribute-requirements" mass-edit job to finish
    Then attribute "Length" should be required in family "4_blocks" for channel "Mobile"
    And attribute "Length" should be required in family "2_blocks" for channel "Mobile"
