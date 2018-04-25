@javascript
Feature: Edit a product product model
  In order to modify the catalog
  As a product manager
  I need to be able edit a product model

  Background:
    Given a "catalog_modeling" catalog configuration
    And I am logged in as "Julia"
    And I am on the "description" attribute page
    And I check the "Read only" switch
    And I save the attribute
    And I should not see the text "There are unsaved changes."

  Scenario: Read-only attributes on product-models are not editable
    When I edit the "dressshoe" product model
    Then the field Model description should be disabled
