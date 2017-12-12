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

  Scenario: Successfully add an association
    When I press the "Add associations" button
    And I check the row "patrick"
    Then the item picker basket should contain patrick
    And I press the "Confirm" button in the popin
    Then I should see product "patrick"

  Scenario: Successfully delete an association
    When I press the "Add associations" button
    And I check the row "patrick"
    Then the item picker basket should contain patrick
    And I press the "Confirm" button in the popin
    Then I should see product "patrick"
    When I select rows patrick
    Then I should see the text "There are unsaved changes"
    And I should not see product "patrick"
    When I save the product
    Then I should not see product "patrick"

  Scenario: Successfully add a product model as association
    When I press the "Add associations" button
    And I check the row "Elegance"
    Then the item picker basket should contain Elegance
    And I press the "Confirm" button in the popin
    Then I should see product "Elegance"

  Scenario: Successfully delete a product model as association
    When I press the "Add associations" button
    And I check the row "Elegance"
    Then the item picker basket should contain Elegance
    And I press the "Confirm" button in the popin
    Then I should see product "Elegance"
    When I select rows Elegance
    Then I should see the text "There are unsaved changes"
    And I should not see product "Elegance"
    When I save the product
    Then I should not see product "Elegance"
