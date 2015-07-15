@javascript
Feature: Validate identifier attribute of a product
  In order to keep my data consistent
  As a regular user
  I need to be able to see validation errors for identifier attribute

  Background:
    Given the "clothing" catalog configuration
    And the following product:
      | sku | categories        |
      | foo | summer_collection |
      | bar | summer_collection |
    And I am logged in as "Mary"

  Scenario: Validate the unique constraint of identifier attribute
    Given I am on the "foo" product page
    And I change the SKU to "bar"
    And I save the product
    Then I should see validation error "The value bar is already set on another product for the unique attribute sku"
    And there should be 1 error in the "Product information" tab

  Scenario: Validate the max characters constraint of identifier attribute
    Given I edit the "sku" attribute
    And I change the "Max characters" to "10"
    And I save the attribute
    When I am on the "foo" product page
    And I change the SKU to "sku-0000000"
    And I save the product
    Then I should see validation error "This value is too long. It should have 10 characters or less."
    And there should be 1 error in the "Product information" tab

  Scenario: Validate the regexp validation rule constraint of identifier attribute
    Given I edit the "sku" attribute
    And I change the "Validation rule" to "Regular expression"
    And I change the "Validation regexp" to "/^sku-\d*$/"
    And I save the attribute
    When I am on the "foo" product page
    And I change the SKU to "001"
    And I save the product
    Then I should see validation error "This value is not valid."
    And there should be 1 error in the "Product information" tab
