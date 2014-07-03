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
