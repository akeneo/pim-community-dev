@javascript
Feature: Invalidate properly the frontend cache to always have the freshest data
  In order be able to edit a product with the latest updated data
  As a product manager
  I need to be able to see the last structure data

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    And the following product:
      | sku   | family |
      | boots | boots  |

  Scenario: Successfully clear the cache when updating a family label
    Given I am on the "boots" product page
    Then I should see the text "Boots"
    Then I am on the "Boots" family page
