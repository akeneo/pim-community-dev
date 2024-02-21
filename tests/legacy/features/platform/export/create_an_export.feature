@javascript
Feature: Create an export
  In order to use my PIM data into my front applications
  As an administrator
  I need to be able to create export jobs

  Background:
    Given the "default" catalog configuration
    And I am logged in as "Peter"
    And I am on the exports grid

  Scenario: Successfully create an export
    Given I create a new export
    And I should see the Code, Label and Job fields
    When I fill in the following information in the popin:
      | Code  | PRODUCT_EXPORT        |
      | Label | Products export       |
      | Job   | Product export in CSV |
    And I press the "Save" button in the popin
    And I should not see the text "There are unsaved changes"
    And I am on the exports grid
    And the grid should contain 1 element
    And I should see export profile Products export
