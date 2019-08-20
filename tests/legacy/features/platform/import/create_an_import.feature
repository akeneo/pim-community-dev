@javascript
Feature: Create an import
  In order to use my PIM data into my front applications
  As an administrator
  I need to be able to create import jobs

  Background:
    Given the "default" catalog configuration
    And I am logged in as "Peter"
    And I am on the imports page

  Scenario: Successfully create an import
    Given I create a new import
    And I should see the Code, Label and Job fields
    When I fill in the following information in the popin:
      | Code  | PRODUCT_IMPORT        |
      | Label | Products import       |
      | Job   | Product import in CSV |
    And I press the "Save" button in the popin
    And I should not see the text "There are unsaved changes"
    And I am on the imports page
    And the grid should contain 2 element
    And I should see import profile Products import
