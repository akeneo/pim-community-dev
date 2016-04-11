@javascript
Feature: Keep the side panel open when switching product page
  In order to be productive with the product edit form
  As a product manager
  I need to be able to keep the panel open on page navigation

  Background:
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    And the following product:
      | sku        |
      | rangers    |
      | high-heels |

  Scenario: I keep the panel open between two product navigation
    Given I am on the "rangers" product page
    And I open the "Completeness" panel
    Then I should see the text "No family defined. Please define a family to calculate the completeness of this product."
    And I am on the "high-heels" product page
    Then I should see the text "No family defined. Please define a family to calculate the completeness of this product."
    Given I close the "Completeness" panel
    And I am on the "rangers" product page
    Then I should see the text "rangers"
    Then I should not see the text "No family defined. Please define a family to calculate the completeness of this product."
