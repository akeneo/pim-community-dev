@javascript
Feature: Product creation
  In order to add a non-imported product
  As a product manager
  I need to be able to manually create a product

  Scenario: Successfully display an error message when user have no access rights on the identifier attribute
    Given the "default" catalog configuration
    And the following attribute group accesses:
      | attribute group | user group | access | group | type             |
      | other           | Manager    | none   | other | pim_catalog_text |
    And I am logged in as "Julia"
    When I am on the products page
    And I create a new product
    Then I should see the text "No attribute is configured as a product identifier or you don't have the rights to edit it."
