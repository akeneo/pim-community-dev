@javascript
Feature: Add attributes to a product
  In order to provide more information about a product restricting accesses
  As a product manager
  I need to be able to add only attributes I have access to

  Background:
    Given a "clothing" catalog configuration
    And the following products:
      | sku     | family  |
      | jacket  | jackets |
    And I am logged in as "Julia"

  Scenario: Successfully display only attributes I have edit permissions access
    Given I am on the "jacket" product page
    Then I should see available attribute Length in group "Sizes"
    And I should not see available attribute Video in group "Media"
