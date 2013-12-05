Feature: Edit an export
  In order to manage existing export jobs
  As a user
  I need to be able to edit an export job

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "admin"

  Scenario: Successfully edit an export job
    Given I am on the "footwear_product_export" export job edit page
    Then I should see the Code field
    And the field Code should be disabled
    When I fill in the following information:
      | Label | My export |
    And I press the "Save" button
    Then I should see "My export"

  @javascript
  Scenario: Successfully update export job configuration
    Given I am on the "footwear_product_export" export job edit page
    Then I should see the Channel, Delimiter, Enclosure, With header and File path fields
    When I fill in the following information:
      | Channel   | Tablet    |
      | Delimiter | \|        |
      | Enclosure | '         |
      | File path | /file.csv |
    And I uncheck the "With header" switch
    And I press the "Save" button
    Then I should see "Channel tablet"
    And I should see "File path /file.csv"
    And I should see "Delimiter |"
    And I should see "Enclosure '"
    And I should see "With header No"

  @javascript
  Scenario: Successfully display a message when there are unsaved changes
    Given I am on the "footwear_product_export" export job edit page
    When I fill in the following information:
      | Label | My export |
    Then I should see "There are unsaved changes."
    When I click on the Akeneo logo
    Then I should see a confirm dialog with the following content:
      | title   | Are you sure you want to leave this page?                           |
      | content | You will lose changes to the export profile if you leave this page. |
