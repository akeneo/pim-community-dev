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
    When I select the "Caterpillar boots" variant group
    And I move on to the next step
    Then "caterpillar_boots" group should contain "boots, moon_boots, sneakers and gold_sandals"
    When I am on the dashboard page
    Then I should have 1 new notification
    And I should see notification:
      | type    | message            |
      | success | Mass edit finished |

  @javascript
  Scenario: BlaBla blublu
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
    When I am on the dashboard page
    Then I should have 1 new notification
    And I should see notification:
      | type    | message                          |
      | warning | Mass edit finished with warnings |
    Then I go on the last executed job resume of "update_product_value"
    And I should see "The product \"gold_watch\" is in the variant group \"caterpillar_boots\" but it misses the following axes: size, color.: gold_watch"
    And I should see "Group \"[caterpillar_boots]\" already contains another product with values \"\": gold_watch"
    And I should see "The product \"laptop\" is in the variant group \"caterpillar_boots\" but it misses the following axes: size, color.: laptop"
    And I should see "Group \"[caterpillar_boots]\" already contains another product with values \"\": laptop"
    And I should see "skipped products 2"
    And I should see "first warnings displayed 4/4"
