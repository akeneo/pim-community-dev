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

  Scenario: Successfully edit a product and back to the grid
    Given I am on the "sandal" product page
    And I fill in the following information:
      | Name | My Sandal |
    When I press "Save and back to grid" on the "Save" dropdown button
    Then I should be on the products page
    And I should see product sandal
    And the row "sandal" should contain:
      | column | value     |
      | sku    | sandal    |
      | name   | My Sandal |

  Scenario: Display a message when form submission fails and I try to leave the page
    Given I am on the "sandal" product page
    And I visit the "Marketing" group
    Given I change the "$ Price" to "wrong value"
    And I save the product
    Then I should see flash message "Please check your entry and try again"
    Then I click back to grid
    And I should see a confirm dialog with the following content:
      | title   | Are you sure you want to leave this page?                   |
      | content | You will lose changes to the product if you leave the page. |

  Scenario: Display a message when I try to leave the page and there are unsaved values
    Given I am on the "sandal" product page
    And I visit the "Marketing" group
    Given I change the "$ Price" to "1234"
    Then I click back to grid
    And I should see a confirm dialog with the following content:
      | title   | Are you sure you want to leave this page?                   |
      | content | You will lose changes to the product if you leave the page. |

  Scenario: Successfully edit a product and create a new one
    Given I am on the "sandal" product page
    And I fill in the following information:
      | Name | My Sandal |
    When I press "Save and create" on the "Save" dropdown button
    Then I should be on the product "sandal" edit page
    And I fill in the following information in the popin:
      | SKU    | sandal_2 |
      | Family | Sandals  |
    And I press the "Save" button in the popin
    Then I should be on the product "sandal_2" edit page
