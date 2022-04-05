@javascript
Feature: Reference entities are only available if the feature is enabled

Background:
  Given a "default" catalog configuration
  And a record job import in CSV
  And a record job import in XLSX
  And I am logged in as "Julia"

Scenario: Reference entities are not available when deactivated
  Given I am on the dashboard page
  Then I should see the text "Activity"
  And I should not see the text "Entities"
  When I am on the "test_csv" import job page
  Then I should not see the text "Import profile - Asset Manager CSV import"
  When I am on the "test_xlsx" import job page
  Then I should not see the text "Import profile - Record XLSX import"
  When I am on the attributes page
  And I create a new attribute
  Then I should see the text "Text"
  And I should not see the text "Reference entity"

@reference-entity-feature-enabled
Scenario: Asset feature is available when activated
  Given I am on the dashboard page
  Then I should see the text "Activity"
  And I should see the text "Entities"
  When I am on the "test_csv" import job page
  Then I should see the text "Import profile - Record CSV import"
  When I am on the "test_xlsx" import job page
  Then I should see the text "Import profile - Record XLSX import"
  When I am on the attributes page
  And I create a new attribute
  Then I should see the text "Text"
  And I should see the text "Reference entity"
