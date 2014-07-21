@javascript
Feature: Product creation
  In order to add a non-imported product
  As a product manager
  I need to be able to manually create a product

  Background:
    Given a "footwear" catalog configuration
    And the following products:
    | sku     | family  |
    | sandals | sandals |
    | boots   |         |
    And I am logged in as "Julia"
    And I am on the products page

  Scenario: Successfully create a product
    Given I create a new product
    Then I should see the SKU and Family fields
    And I fill in the following information in the popin:
      | SKU | caterpillar_1 |
    And I press the "Save" button in the popin
    Then I should see "Family: N/A"
    And I should see "caterpillar_1"

  Scenario: Fail to create a product with an already used code
    Given I create a new product
    And I fill in the following information in the popin:
      | SKU | sandals |
    And I press the "Save" button in the popin
    Then I should see validation error "This value is already set on another product."
