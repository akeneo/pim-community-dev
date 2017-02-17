@javascript
Feature: Edit a boolean value
  In order to enrich the catalog
  As a regular user
  I need to be able edit boolean values of a product

  Background:
    Given the "default" catalog configuration
    And a "tshirt" product
    And the following attributes:
      | code             | label-en_US      | type                | scopable | localizable | group |
      | boolean          | Boolean          | pim_catalog_boolean | 0        | 0           | other |
      | scopable_boolean | Scopable boolean | pim_catalog_boolean | 1        | 1           | other |
    And I am logged in as "Mary"
    And I am on the "tshirt" product page
    And I add available attributes Boolean and Scopable boolean
    And I save the product
    Then I should not see the text "There are unsaved changes."

  Scenario: Successfully update a boolean value
    When I check the "Boolean" switch
    And I press the "Save" button
    Then I should not see the text "There are unsaved changes."
    And attribute boolean of "tshirt" should be "true"
    When I uncheck the "Boolean" switch
    And I press the "Save" button
    Then I should not see the text "There are unsaved changes."
    And attribute boolean of "tshirt" should be "false"

  Scenario: Successfully update a scopable boolean value
    Given I switch the scope to "ecommerce"
    When I check the "Scopable boolean" switch
    And I press the "Save" button
    Then I should not see the text "There are unsaved changes."
    And the english ecommerce scopable_boolean of "tshirt" should be "true"
    When I uncheck the "Scopable boolean" switch
    And I press the "Save" button
    Then I should not see the text "There are unsaved changes."
    And the english ecommerce scopable_boolean of "tshirt" should be "false"
