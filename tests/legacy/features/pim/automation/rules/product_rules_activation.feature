@javascript
Feature: Product rules are only available if the feature is enabled

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"

  @data_quality_insights-feature-enabled @data_quality_insights_all_criteria-feature-enabled
  Scenario: Do not display the smart attribute when feature is disabled
    Given I am on the attributes page
    Then I should see the columns Label, Type, Group, Scopable, Localizable and Quality

  Scenario: Do not display the tab rules in attributes
    Given I am on the "description" attribute page
    Then I should see the text "Properties"
    Then I should not see the text "Rules"
