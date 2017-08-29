@javascript
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

  Scenario: Add products to a variant group
    Given I am logged in as "Julia"
    And I am on the products grid
    And I select rows moon_boots, gold_sandals and sneakers
    And I press "Change product information" on the "Bulk Actions" dropdown button
    And I choose the "Add to a variant group" operation
    When I select the "Caterpillar boots" variant group
    And I confirm mass edit
    And I wait for the "add_to_variant_group" job to finish
    Then "caterpillar_boots" group should contain "boots, moon_boots, sneakers and gold_sandals"
    When I am on the dashboard page
    Then I should have 1 new notification
    And I should see notification:
      | type    | message                            |
      | success | Mass add products to variant group |
    And I am on the "caterpillar_boots" variant group page
    And I should see the text "Products: 4"

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
    And I am on the products grid
    When I select rows gold_watch, laptop
    And I press "Change product information" on the "Bulk Actions" dropdown button
    And I choose the "Add to a variant group" operation
    When I select the "Caterpillar boots" variant group
    And I confirm mass edit
    And I wait for the "add_to_variant_group" job to finish
    Then "caterpillar_boots" group should contain "boots, moon_boots"
    And I am on the dashboard page
    And I should have 1 new notification
    And I should see notification:
      | type    | message                            |
      | warning | Mass add products to variant group |
    Then I go on the last executed job resume of "add_to_variant_group"
    And I should see the text "skipped products 2"
    And I should see the text "first warnings displayed 2/2"
    And I should see the text "You cannot group the following product because it is already in a variant group or doesn't have the group axis."
    And I am on the "caterpillar_boots" variant group page
    And I should see the text "Products: 2"

  Scenario: Add products to a variant group with duplicated variant axis values in selection (and not yet in variant group)
    And I am logged in as "Julia"
    And I am on the products grid
    When I select rows gold_sandals, gold_boots
    And I press "Change product information" on the "Bulk Actions" dropdown button
    And I choose the "Add to a variant group" operation
    When I select the "Caterpillar boots" variant group
    And I confirm mass edit
    And I wait for the "add_to_variant_group" job to finish
    Then "caterpillar_boots" group should contain "boots, moon_boots"
    And I am on the dashboard page
    And I should have 1 new notification
    And I should see notification:
      | type    | message                            |
      | warning | Mass add products to variant group |
    Then I go on the last executed job resume of "add_to_variant_group"
    And I should see the text "skipped products 2"
    And I should see the text "first warnings displayed 2/2"
    And I should see the text "Product can't be set in the selected variant group: duplicate variation axis values with another product in selection"
    And I am on the "caterpillar_boots" variant group page
    And I should see the text "Products: 2"
