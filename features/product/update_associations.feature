@javascript
Feature: Update the product associations
  In order to associate products with other products
  As a product manager
  I need to update the product associations

  Background:
    Given the "apparel" catalog configuration
    And I am logged in as "Julia"
    And the following products:
      | sku       | family  | categories |
      | spongebob | tshirts | men_2013   |
      | patrick   | tshirts | men_2013   |
    And I am on the "spongebob" product page
    And I visit the "Associations" column tab

  Scenario: Successfully add an association
    When I press the "Add associations" button and wait for modal
    And I check the row "patrick"
    Then the item picker basket should contain patrick
    And I press the "Confirm" button
    And I save the product
    Then the rows "patrick" should be checked
