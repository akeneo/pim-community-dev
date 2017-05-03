@javascript
Feature: Product edition clicking on another action
  In order to optimize time to create and enrich products
  As a regular user
  I need to be able to save my product and be redirect where I want

  Background:
    Given a "footwear" catalog configuration
    And the following products:
      | sku    | family  |
      | sandal | sandals |
    And I am logged in as "Mary"
    And I am on the products page
    And I display the columns SKU, Name, Description and Family

  Scenario: Successfully edit a product and back to the grid
    Given I am on the "sandal" product page
    And I fill in the following information:
      | Name | My Sandal |
    When I save and back to the grid
    Then I should be on the products page
    And I should see product sandal
    And the row "sandal" should contain:
      | column | value     |
      | sku    | sandal    |
      | name   | My Sandal |

  @skip-nav
  Scenario: Display a message when form submission fails and I try to leave the page
    Given I am on the "sandal" product page
    And I visit the "Marketing" group
    Given I fill in the following information:
      | Price | foo EUR |
    And I save the product
    Then I should see the flash message "The product could not be updated."
    Then I click back to grid
    And I should see "You will lose changes to the product if you leave the page." in popup

  @skip-nav
  Scenario: Display a message when I try to leave the page and there are unsaved values
    Given I am on the "sandal" product page
    And I visit the "Marketing" group
    Given I fill in the following information:
      | Price | 1234 USD |
    Then I click back to grid
    And I should see "You will lose changes to the product if you leave the page." in popup
