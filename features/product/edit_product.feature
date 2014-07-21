@javascript
Feature: Edit a product
  In order to enrich the catalog
  As a regular user
  I need to be able edit and save a product

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Mary"
    And the following products:
      | sku    | family  |
      | sandal | sandals |

  Scenario: Successfully create, edit and save a product
    Given I am on the "sandal" product page
    And I fill in the following information:
      | Name | My Sandal |
    When I press the "Save" button
    Then I should be on the product "sandal" edit page
    Then the product Name should be "My Sandal"
