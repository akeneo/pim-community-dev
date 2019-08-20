@javascript
Feature: Edit an export
  In order to manage existing export jobs
  As an administrator
  I need to be able to edit an export job

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Peter"

  Scenario: Successfully update export job configuration
    Given I am on the "csv_footwear_product_export" export job edit page
    When I visit the "Global settings" tab
    Then I should see the Delimiter, Enclosure, With header, File path and Decimal separator fields
    And I fill in the following information:
      | Delimiter         | \|            |
      | Enclosure         | '             |
      | File path         | /tmp/file.csv |
      | Decimal separator | ,             |
      | Date format       | yyyy-mm-dd    |
    And I uncheck the "With header" switch
    When I visit the "Content" tab
    Then I should see the Channel, Locales fields
    Then I should see the filters enabled, completeness, updated, sku and family
    And I fill in the following information:
      | Channel | Tablet |
    Then I filter by "enabled" with operator "" and value "Disabled"
    And I filter by "family" with operator "" and value "Boots"
    And I filter by "completeness" with operator "Not complete on all selected locales" and value ""
    And I filter by "sku" with operator "" and value "identifier1 identifier2,identifier3 ,identifier4"
    Then I press the "Save" button
    Then I should not see the text "There are unsaved changes"
    And I press the "Edit" button
    When I visit the "Global settings" tab
    Then I should see the text "File path"
    And the "File path" field should contain "/tmp/file.csv"
    And I should see the text "Delimiter"
    And the "Delimiter" field should contain "|"
    And I should see the text "Enclosure"
    And the "Enclosure" field should contain "'"
    And I should see the text "With header"
    And the "With header" field should contain ""
    And I should see the text "Decimal separator"
    And I should see the text "comma (,)"
    And I should see the text "Date format"
    And I should see the text "yyyy-MM-dd"
    When I visit the "Content" tab
    Then I should see the text "Channel (required) Tablet"
    And I should see the text "Status disabled"
    And I should see the text "English (United States)"
    And I should see the text "Boots"
    And I should see the text "Not complete on all selected locales"
    And I should see the text "identifier1, identifier2, identifier3, identifier4"
