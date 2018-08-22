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

  Scenario: Validate the max characters constraint of identifier attribute
    Given I am on the "sku" attribute page
    And I change the "Max characters" to "10"
    And I save the attribute
    And I should not see the text "There as unsaved changes"
    When I am on the "foo" product page
    And I change the SKU to "sku-0000000"
    And I save the product
    Then I should see validation tooltip "This value is too long. It should have 10 characters or less."
    And there should be 1 error in the "Other" tab

  Scenario: Validate the regexp validation rule constraint of identifier attribute
    Given I am on the "sku" attribute page
    And I change the "Validation rule" to "Regular expression"
    And I change the "Regular expression" to "/^sku-\d*$/"
    And I save the attribute
    And I should not see the text "There are unsaved changes"
    When I am on the "foo" product page
    And I change the SKU to "001"
    And I save the product
    Then I should see validation tooltip "This value is not valid."
    And there should be 1 error in the "Other" tab

  @jira https://akeneo.atlassian.net/browse/PIM-3447
  Scenario: Validate the max database value length of identifier attribute
    Given I am on the "bar" product page
    When I change the SKU to an invalid value
    And I save the product
    Then I should see validation tooltip "This value is too long. It should have 255 characters or less."
    And there should be 1 error in the "Other" tab
