Feature: Edit a job
  In order to manage existing jobs
  As a user
  I need to be able to edit a job

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "admin"

  Scenario: Successfully edit a job
    Given I am on the "footwear_product_import" import job edit page
    Then I should see the Code field
    And the field Code should be disabled
    When I fill in the following information:
      | Label | My import |
    And I press the "Save" button
    Then I should see "My import"

  @javascript
  Scenario: Successfully display a message when there are unsaved changes
    Given I am on the "footwear_product_export" export job edit page
    When I fill in the following information:
      | Label | My export |
    Then I should see "There are unsaved changes."
    And I click on the Akeneo logo
    Then I should see a confirm dialog with the following content:
      | title   | Are you sure you want to leave this page?                           |
      | content | You will lose changes to the export profile if you leave this page. |
