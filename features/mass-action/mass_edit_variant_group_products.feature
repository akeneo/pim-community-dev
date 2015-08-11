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
      | gold_boots   | sandals  |                   | 42   | white |

  @javascript
  Scenario: Add products to a variant group
    Given I am logged in as "Julia"
    And I am on the products page
    And I mass-edit products moon_boots, gold_sandals and sneakers
    And I choose the "Add to a variant group" operation
    When I select the "Caterpillar boots" variant group
    And I move on to the next step
    And I wait for the "add-to-variant-group" mass-edit job to finish
    Then "caterpillar_boots" group should contain "boots, moon_boots, sneakers and gold_sandals"
    When I am on the dashboard page
    Then I should have 1 new notification
    And I should see notification:
      | type    | message                                               |
      | success | Mass edit Mass add products to variant group finished |

  @javascript
  Scenario: Add products to a variant group with invalid axis
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
    When I select the "Caterpillar boots" variant group
    And I move on to the next step
    And I wait for the "add-to-variant-group" mass-edit job to finish
    Then I should have 1 new notification
    And I should see notification:
      | type    | message                                                                  |
      | warning | Mass edit Mass add products to variant group finished with some warnings |
    Then I go on the last executed job resume of "add_to_variant_group"
    And I should see "skipped products 2"
    And I should see "first warnings displayed 2/2"
    And I should see "EXCLUDED PRODUCT"
    And I should see "You cannot group the following product because it is already in a variant group or doesn't have the group axis."

  @javascript
  Scenario: Add products to a variant group with duplicated variant axis values in selection (and not yet in variant group)
    And I am logged in as "Julia"
    And I am on the products page
    When I mass-edit products gold_sandals, gold_boots
    And I choose the "Add to a variant group" operation
    When I select the "Caterpillar boots" variant group
    And I move on to the next step
    And I wait for the "add-to-variant-group" mass-edit job to finish
    Then I should have 1 new notification
    And I should see notification:
      | type    | message                                                                  |
      | warning | Mass edit Mass add products to variant group finished with some warnings |
    Then I go on the last executed job resume of "add_to_variant_group"
    And I should see "skipped products 2"
    And I should see "first warnings displayed 2/2"
    And I should see "DUPLICATED AXIS"
    And I should see "Product can't be set in the selected variant group: duplicate variation axis values with another product in selection"
