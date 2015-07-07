Feature: Apply restrictions when mass editing products with variant groups
  In order to keep integrity logic on variant goup products in mass edit
  As a product manager
  I need to be restricted when making mass edit changes with these products

  Background:
    Given a "footwear" catalog configuration
    And the following products:
      | sku          | family   | groups            | size | color |
      | boots        | boots    | caterpillar_boots | 41   | blue  |
      | moon_boots   | boots    | caterpillar_boots | 43   | blue  |
      | sneakers     | sneakers |                   | 42   | red   |
      | sandals      | sandals  |                   | 42   | blue  |
      | gold_sandals | sandals  |                   | 42   | white |

  @javascript
  Scenario: Add products to a variant group
    Given I am logged in as "Julia"
    And I am on the products page
    And I mass-edit products moon_boots, gold_sandals and sneakers
    And I choose the "Add to a variant group" operation
    Then I should see:
    """
    You cannot group the following products (moon_boots) because they are already in a variant group or doesn't have the group axis.
    """
    When I select the "Caterpillar boots" variant group
    And I move on to the next step
    Then "caterpillar_boots" group should contain "boots, moon_boots, sneakers and gold_sandals"

  @javascript
  Scenario: Filters variant groups not having all their attributes in common with products
    Given the following product groups:
      | code        | label            | axis                  | type    |
      | magic_shoes | Some magic shoes | length, color         | VARIANT |
      | ultra_shoes | Ultra shoes      | handmade, color, size | VARIANT |
    And I am logged in as "Julia"
    And I am on the products page
    When I mass-edit products moon_boots, gold_sandals and sneakers
    And I choose the "Add to a variant group" operation
    Then I should see:
    """
    The following variant groups have been skipped because they don't share attributes with selected products : Similar boots [similar_boots], Some magic shoes [magic_shoes], Ultra shoes [ultra_shoes]
    """
    And the field "Group" should have the following options:
    """
    Caterpillar boots
    """
    When I select the "Caterpillar boots" variant group
    And I move on to the next step
    Then "caterpillar_boots" group should contain "boots, moon_boots, sneakers and gold_sandals"

  @javascript
  Scenario: No valid variant group based on product attributes is available for mass assign
    Given the following families:
      | code      |
      | computers |
      | watches   |
    And the following products:
      | sku        | family    |
      | gold_watch | watches   |
      | laptop     | computers |
    And I am logged in as "Julia"
    And I am on the products page
    When I mass-edit products gold_watch, laptop
    And I choose the "Add to a variant group" operation
    Then I should see:
    """
    No variant group is sharing attributes with selected products.
    """
    And I should see:
    """
    The following variant groups have been skipped because they don't share attributes with selected products : Caterpillar boots [caterpillar_boots]
    """

  @javascript
  Scenario: No variant group is available for mass assign
    Given I am logged in as "Julia"
    And I am on the variant groups page
    And I click on the "Delete" action of the row which contains "caterpillar_boots"
    And I confirm the deletion
    And I am on the products page
    When I mass-edit products moon_boots, sandals and sneakers
    And I choose the "Add to a variant group" operation
    Then I should see:
    """
    No variant group for now. Please start by creating a variant group.
    """
