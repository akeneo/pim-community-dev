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
  And I mass-edit products moon_boots, sandals and sneakers
  And I choose the "Add to a variant group" operation
  Then I should see:
  """
  You cannot group the following products (moon_boots) because they are already in a variant group or doesn't have the group axis.
  """
  When I select the "Caterpillar boots" variant group
  And I move on to the next step
  Then "caterpillar_boots" group should contain "boots, moon_boots, sneakers and sandals"

@javascript
Scenario: Filters variant groups not having all their attributes in common with products
  Given the following product groups:
    | code        | label            | attributes         | type    |
    | magic_shoes | Some magic shoes | name, color        | VARIANT |
    | ultra_shoes | Ultra shoes      | price, color, size | VARIANT |
  And the following variant group values:
    | group       | attribute | value            | locale | scope |
    | magic_shoes | name      | The magical ones | en_US  |       |
    | magic_shoes | color     | blue             |        |       |
    | ultra_shoes | price-EUR | 10.0             |        |       |
    | ultra_shoes | color     | red              |        |       |
    | ultra_shoes | size      | 42               |        |       |
  And I am logged in as "Julia"
  And I am on the products page
  When I mass-edit products moon_boots, sandals and sneakers
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
  Then "caterpillar_boots" group should contain "boots, moon_boots, sneakers and sandals"

  # Scenario: A warning must be raised if there is no valid variant group based on product attributes

  # Scenario: A warning must be raised if there is no variant group