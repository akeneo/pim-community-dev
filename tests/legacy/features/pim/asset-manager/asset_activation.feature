@javascript
Feature: Assets are only available if the feature is enabled

  Background:
    Given a "default" catalog configuration
    And an asset manager job import in CSV
    And I am logged in as "Julia"
    And the following products:
      | sku     |
      | rangers |

  Scenario: Asset feature is not available when deactivated
    Given I am on the dashboard page
    Then I should see the text "Activity"
    And I should not see the text "Assets"
    When I am on the "test_csv" import job page
    Then I should not see the text "Import profile - Asset Manager CSV import"
    When I am on the attributes page
    And I create a new attribute
    Then I should see the text "Text"
    And I should not see the text "Asset"
    When I am on the "rangers" product page
    And I should not see the text "Asset"

  @asset-manager-feature-enabled
  Scenario: Asset feature is available when activated
    Given I am on the dashboard page
    And I should see the text "Assets"
    When I am on the "test_csv" import job page
    Then I should see the text "Import profile - Asset Manager CSV import"
    When I am on the attributes page
    And I create a new attribute
    And I should see the text "Asset collection"
    When I am on the "rangers" product page
    And I should see the text "Asset"
