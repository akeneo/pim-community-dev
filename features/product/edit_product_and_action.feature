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
    And I display the columns sku, name, image, description and family

  @unstable-app
  Scenario: Successfully edit a product and back to the grid
    Given I am on the "sandal" product page
    And I fill in the following information:
      | Name | My Sandal |
    When I press "Save and back" on the "Save" dropdown button
    Then I should be on the products page
    And I should see product sandal
    And the row "sandal" should contain:
      | column | value     |
      | sku    | sandal    |
      | name   | My Sandal |

  Scenario: Display a message when form submission fails and I try to leave the page
    Given I am on the "sandal" product page
    And I visit the "Marketing" group
    Given I fill in the following information:
      | Price | foo EUR |
    And I save the product
    Then I should see flash message "Please check your entry and try again"
    Then I press the "Back to grid" button
    And I should see a confirm dialog with the following content:
      | title   | Are you sure you want to leave this page?                   |
      | content | You will lose changes to the product if you leave the page. |

  Scenario: Display a message when I try to leave the page and there are unsaved values
    Given I am on the "sandal" product page
    And I visit the "Marketing" group
    Given I fill in the following information:
      | Price | 1234 USD |
    Then I press the "Back to grid" button
    And I should see a confirm dialog with the following content:
      | title   | Are you sure you want to leave this page?                   |
      | content | You will lose changes to the product if you leave the page. |
