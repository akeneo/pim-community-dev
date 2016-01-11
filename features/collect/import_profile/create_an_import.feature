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
    And I press the "Save" button
    Then I click back to grid
    And the grid should contain 1 element
    And I should see import profile PRODUCT_IMPORT

  Scenario: Fail to create a job import
    Given I create a new import
    When I fill in the following information in the popin:
      | Code  | PRODUCT_IMPORT  |
      | Label | Products import |
    And I press the "Save" button
    Then I should see validation error "Failed to create an import with an unknown job definition"

