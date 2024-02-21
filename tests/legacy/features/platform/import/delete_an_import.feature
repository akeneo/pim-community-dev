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

  Scenario: Successfully delete a CSV import job from the job edit page
    Given I am on the "csv_footwear_product_import" import job edit page
    When I press the secondary action "Delete"
    And I confirm the deletion
    Then I should see the flash message "Job instance successfully removed"
    And the grid should contain 24 elements
    And I should not see import profile "CSV footwear product import"
