@javascript
Feature: Product creation
  In order to add a non-imported product
  As a user
  I need to be able to manually create a product

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "admin"
    And I am on the products page

  Scenario: Successfully create a product
    Given I create a new product
    Then I should see the SKU and Family fields
    And I fill in the following information in the popin:
      | SKU | caterpillar_1 |
    And I press the "Save" button
    Then I should see "Family: N/A"
    And I should see "caterpillar_1"
