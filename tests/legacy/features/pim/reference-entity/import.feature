@javascript
Feature: Display record import profile job pages
  In order to enrich my catalog
  As a regular user
  I need to be able to display the record profile import jobs

  Background:
    Given a "default" catalog configuration
    And a record job import in CSV
    And a record job import in XLSX
    And I am logged in as "Julia"

  Scenario: Successfully display a record import profile job in CSV
    Given I am on the "test_csv" import job page
    Then I should see the text "Import profile - Record CSV import"

  Scenario: Successfully display a record import profile job in XLSX
    Given I am on the "test_xlsx" import job page
    Then I should see the text "Import profile - Record XLSX import"
