@javascript
Feature: Create product models through CSV import
  In order to setup my application
  As an administrator
  I need to be able to import profiles

  Background:
    Given the "catalog_modeling" catalog configuration
    And I am logged in as "Peter"
    And I am on the imports grid

  Scenario: Peter creates a new CSV import profile to import products models
    Given I create a new import
    When I fill in the following information in the popin:
      | Code  | product_model_import        |
      | Label | Product model import in CSV |
      | Job   | Product model import in CSV |
    And I press the "Save" button
    Then I should not see the text "There are unsaved changes"
    And I visit the "Global settings" tab
    And I should see the Family variant fields
