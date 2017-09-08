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
    When I am on the products grid
    And I select all entities
    And I press "Change product information" on the "Bulk Actions" dropdown button
    And I choose the "Edit common attributes" operation
    And I move on to the choose step
    And I choose the "Edit common attributes" operation
    And I display the Name attribute
    And I change the "Name" to "Same product"
    And I confirm mass edit
    And I wait for the "edit_common_attributes" job to finish
    Then the product "super_boots" should have the following values:
      | name-en_US | Same product |
    And the product "ultra_boots" should have the following values:
      | name-en_US | Same product |
    And the product "sandals" should have the following values:
      | name-en_US | Same product |

  Scenario: Edit family of all products, filtered by category and completeness
    Given I launched the completeness calculator
    When I am on the products grid
    And I hide the filter "family"
    And I open the category tree
    And I filter by "category" with operator "" and value "2014_collection"
    And I filter by "category" with operator "" and value "winter_collection"
    And I close the category tree
    And I switch the scope to "Mobile"
    And I filter by "completeness" with operator "" and value "yes"
    When I select all entities
    And I press "Change product information" on the "Bulk Actions" dropdown button
    And I choose the "Change family" operation
    And I change the Family to "Sandals"
    And I confirm mass edit
    And I wait for the "update_product_value" job to finish
    Then the family of product "super_boots" should be "sandals"
    Then the family of product "mega_boots" should be "sandals"
    And the family of product "ultra_boots" should be "boots"

  Scenario: Edit all families, filtered by name
    Given the following families:
      | code       | label-en_US     |
      | 4_blocks   | Lego 4 blocks   |
      | 2_blocks   | Lego 2 blocks   |
      | characters | Lego characters |
    When I am on the families grid
    And I search "blocks"
    Then the grid should contain 2 elements
    And I select all entities
    And I press the "Change product information" button
    And I choose the "Set attributes requirements" operation
    And I display the Length attribute
    And I switch the attribute "length" requirement in channel "mobile"
    And I confirm mass edit
    And I wait for the "set_attribute_requirements" job to finish
    Then attribute "Length" should be required in family "4_blocks" for channel "Mobile"
    And attribute "Length" should be required in family "2_blocks" for channel "Mobile"

  @jira https://akeneo.atlassian.net/browse/PIM-5000
  Scenario: Not applying a mass edit operation on unchecked products after "all" was selected
    Given I am on the products grid
    And I select all entities
    And I unselect row mega_boots
    When I press "Change product information" on the "Bulk Actions" dropdown button
    And I choose the "Change status" operation
    And I move on to the choose step
    And I choose the "Change status" operation
    And I disable the products
    And I wait for the "update_product_value" job to finish
    Then product "super_boots" should be disabled
    And product "ultra_boots" should be disabled
    And product "sandals" should be disabled
    But product "mega_boots" should be enabled
