@javascript
Feature: Create an export
  In order to use my PIM data into my front applications
  As an administrator
  I need to be able to create export jobs

  Background:
    Given the "default" catalog configuration
    And I am logged in as "Peter"
    And I am on the exports page

  Scenario: Successfully create an export
    Given I create a new export
    And I should see the Code, Label and Job fields
    When I fill in the following information in the popin:
      | Code  | PRODUCT_EXPORT        |
      | Label | Products export       |
      | Job   | Product export in CSV |
    And I press the "Save" button
    Then I click back to grid
    And the grid should contain 1 element
    And I should see export profile PRODUCT_EXPORT

  Scenario: Fail to create a job export without code
    Given I create a new export
    When I fill in the following information in the popin:
      | Label | Products export |
    And I press the "Save" button
    Then I should see validation error "This value should not be blank."

  Scenario: Fail to create a job export without job
    Given I create a new export
    When I fill in the following information in the popin:
      | Code  | PRODUCT_EXPORT  |
      | Label | Products export |
    And I press the "Save" button
    Then I should see validation error "Failed to create an export with an unknown job definition"

