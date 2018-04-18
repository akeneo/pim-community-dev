@javascript
Feature: Profile permissions on Export
  In order to edit the profile permission
  As a product manager
  I need to be able to see the permission in edit profile

  Background:
    Given a "clothing" catalog configuration

  @jira https://akeneo.atlassian.net/browse/PIM-7185
  Scenario: Successfully display permissions tab for product csv export
    Given I am logged in as "Julia"
    And I am on the "csv_clothing_product_export" export job edit page
    And I visit the "Permissions" tab
    Then I should see the text "Allowed to execute job profile"

  @jira https://akeneo.atlassian.net/browse/PIM-7185
  Scenario: Not display permissions tab for product csv export if user don't have the right
    Given I am logged in as "Julien"
    And I am on the "csv_clothing_product_export" export job edit page
    Then I should not see the text "Permissions"

  @jira https://akeneo.atlassian.net/browse/PIM-7185
  Scenario: Successfully display permissions tab for product model csv export
    Given I am logged in as "Julia"
    And I am on the "csv_clothing_product_model_export" export job edit page
    And I visit the "Permissions" tab
    Then I should see the text "Allowed to execute job profile"

  @jira https://akeneo.atlassian.net/browse/PIM-7185
  Scenario: Not display permissions tab for product model csv export if user don't have the right
    Given I am logged in as "Julien"
    And I am on the "csv_clothing_product_model_export" export job edit page
    Then I should not see the text "Permissions"

  @jira https://akeneo.atlassian.net/browse/PIM-7185
  Scenario: Successfully display permissions tab for product xlsx export
    Given I am logged in as "Julia"
    And I am on the "xlsx_clothing_product_export" export job edit page
    And I visit the "Permissions" tab
    Then I should see the text "Allowed to execute job profile"

  @jira https://akeneo.atlassian.net/browse/PIM-7185
  Scenario: Not display permissions tab for product xlsx import if user don't have the right
    Given I am logged in as "Julien"
    And I am on the "xlsx_clothing_product_export" export job edit page
    Then I should not see the text "Permissions"

  @jira https://akeneo.atlassian.net/browse/PIM-7185
  Scenario: Successfully display permissions tab for product model xlsx export
    Given I am logged in as "Julia"
    And I am on the "xlsx_clothing_product_model_export" export job edit page
    And I visit the "Permissions" tab
    Then I should see the text "Allowed to execute job profile"

  @jira https://akeneo.atlassian.net/browse/PIM-7185
  Scenario: Not display permissions tab for product model xlsx import if user don't have the right
    Given I am logged in as "Julien"
    And I am on the "xlsx_clothing_product_model_export" export job edit page
    Then I should not see the text "Permissions"

  @jira https://akeneo.atlassian.net/browse/PIM-7109
  Scenario: Successfully display permissions tab for attribute csv export
    Given I am logged in as "Julia"
    And I am on the "csv_clothing_attribute_export" export job edit page
    And I visit the "Permissions" tab
    Then I should see the text "Allowed to execute job profile"
