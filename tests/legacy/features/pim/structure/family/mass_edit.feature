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

  Scenario: Edit all families, filtered by name
    Given the following families:
      | code       | label-en_US     |
      | 4_blocks   | Lego 4 blocks   |
      | 2_blocks   | Lego 2 blocks   |
      | characters | Lego characters |
    When I am on the families grid
    And I search "blocks"
    Then the grid should contain 2 elements
    And I select rows Lego 2 blocks
    And I select all entities
    And I press the "Bulk actions" button
    And I choose the "Set attributes requirements" operation
    And I display the Length attribute
    And I switch the attribute "length" requirement in channel "mobile"
    And I confirm mass edit
    And I wait for the "set_attribute_requirements" job to finish
    Then attribute "Length" should be required in family "4_blocks" for channel "Mobile"
    And attribute "Length" should be required in family "2_blocks" for channel "Mobile"
