@javascript
Feature: Create product models through CSV import
  In order to setup my application
  As an administrator
  I need to be able to import profil

  Background:
    Given the "catalog_modeling" catalog configuration
    And I am logged in as "Peter"
    And I am on the imports page

  Scenario: Peter creates a new CSV import profile to import products models
    Given I create a new import
    When I fill in the following information in the popin:
      | Code  | product_model_import     |
      | Label | CSV product model import |
      | Job   | CSV product model import |
    And I press the "Save" button
    Then I should not see the text "There are unsaved changes"

  Scenario: Peter creates a new XSLX import profile to import products models
    Given I create a new import
    When I fill in the following information in the popin:
      | Code  | product_model_import     |
      | Label | XSLX product model import |
      | Job   | XSLX product model import |
    And I press the "Save" button
    Then I should not see the text "There are unsaved changes"
