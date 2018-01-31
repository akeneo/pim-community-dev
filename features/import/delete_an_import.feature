@javascript
Feature: Delete import
  In order to delete an import job that have been created
  As an administrator
  I need to be able to view a list of them
  And I need to delete one of them or cancel my operation

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Peter"
    And I am on the imports page

  Scenario: Successfully delete a CSV import job from the jobs page
    Given I delete the "CSV footwear product import" job
    When I confirm the deletion
    Then I should see the flash message "Import profile successfully removed"
    And the grid should contain 24 elements
    And I should not see import profile "CSV footwear product import"

  Scenario: Successfully cancel the deletion of a CSV import job
    Given I delete the "CSV footwear product import" job
    When I cancel the deletion
    Then the grid should contain 25 elements
    And I should see import profile "CSV footwear product import"

  Scenario: Successfully delete a CSV import job from the job edit page
    Given I am on the "csv_footwear_product_import" import job edit page
    When I press the secondary action "Delete"
    And I confirm the deletion
    Then I should see the flash message "Job instance successfully removed"
    And the grid should contain 24 elements
    And I should not see import profile "CSV footwear product import"

  @github https://github.com/akeneo/pim-community-dev/issues/6414
  Scenario: Correctly delete a newly created job profile
    Given I create a new import
    Then I should see the Code, Label and Job fields
    When I fill in the following information in the popin:
      | Code  | test                  |
      | Label | Test                  |
      | Job   | Product import in CSV |
    And I press the "Save" button
    And I press the secondary action "Delete"
    And I confirm the deletion
    Then I should see the flash message "Import profile successfully removed"
    When I am on the imports page
    Then the grid should contain 25 elements
