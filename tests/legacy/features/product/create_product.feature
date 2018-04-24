@javascript
Feature: Product creation
  In order to add a non-imported product
  As a product manager
  I need to be able to manually create a product

  Scenario: Successfully display an error message when user have no access rights on the identifier attribute
    Given the "default" catalog configuration
    And the following attribute group accesses:
      | attribute group | user group | access |
      | other           | Manager    | none   |
    And I am logged in as "Julia"
    When I am on the products grid
    When I create a product
    And I press the "Save" button in the popin
    Then I should see the text "No attribute is configured as a product identifier or you don't have the rights to edit it."
