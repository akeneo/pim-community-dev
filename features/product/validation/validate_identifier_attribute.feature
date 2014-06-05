@javascript
Feature: Validate identifier attribute of a product
  In order to keep my data consistent
  As a regular user
  I need to be able to see validation errors for identifier attribute

  Background:
    Given the "default" catalog configuration
    And a "foo" product
    And a "bar" product
    And I am logged in as "Mary"

  Scenario: Validate the unique constraint of identifier attribute
    Given I am on the "foo" product page
    And I change the SKU to "sku-001"
    And I save the product
    When I am on the "bar" product page
    And I change the SKU to "sku-001"
    And I save the product
    Then I should see validation tooltip "This value is already set on another product."
    And I should see validation tooltip "There are errors in this tab!"
    And the "Attributes" tab should be red

  Scenario: Validate the max characters constraint of identifier attribute
    Given I edit the "sku" attribute
    And I change the "Max characters" to "10"
    And I save the attribute
    When I am on the "foo" product page
    And I change the SKU to "sku-0000000"
    And I save the product
    Then I should see validation tooltip "This value is too long. It should have 10 characters or less."
    And I should see validation tooltip "There are errors in this tab!"
    And the "Attributes" tab should be red

  Scenario: Validate the regexp validation rule constraint of identifier attribute
    Given I edit the "sku" attribute
    And I change the "Validation rule" to "Regular expression"
    And I change the "Validation regexp" to "/^sku-\d*$/"
    And I save the attribute
    When I am on the "foo" product page
    And I change the SKU to "001"
    And I save the product
    Then I should see validation tooltip "This value is not valid."
    And I should see validation tooltip "There are errors in this tab!"
    And the "Attributes" tab should be red
