@javascript
Feature: Update the product associations
  In order to associate products with other products
  As a product manager
  I need to update the product associations

  Background:
    Given the "catalog_modeling" catalog configuration
    And I am logged in as "Julia"
    And the following products:
      | sku       | family   | categories |
      | spongebob | clothing | tshirts    |
      | patrick   | clothing | tshirts    |
    And I am on the "spongebob" product page
    And I visit the "Associations" column tab

  @critical
  Scenario: Successfully add an association
    Given I add associations
    And I search "pat"
    And I check the row "patrick"
    And the item picker basket should contain patrick
    When I press the "Confirm" button in the popin
    Then I should see product "patrick"
    And I should see the text "1 product(s), 0 product model(s) and 0 group(s)"

  Scenario: Successfully delete an association
    Given I add associations
    And I search "pat"
    And I check the row "patrick"
    And the item picker basket should contain patrick
    And I press the "Confirm" button in the popin
    And I should see product "patrick"
    And I remove the row "patrick"
    And I should see the text "There are unsaved changes"
    And I should not see product "patrick"
    When I save the product
    Then I should not see product "patrick"
    And I should see the text "0 product(s), 0 product model(s) and 0 group(s)"

  @critical
  Scenario: Successfully add a product model as association
    Given I should see the text "There are no associated products"
    And I add associations
    And I should see the text "Add Cross sell associations"
    And I should see the text "Belt"
    And I search "juno"
    And I check the row "juno"
    And the item picker basket should contain juno
    When I press the "Confirm" button in the popin
    Then I should see product "juno"
    And I should see the text "0 product(s), 1 product model(s) and 0 group(s)"
