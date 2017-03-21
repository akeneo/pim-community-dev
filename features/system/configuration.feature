@javascript
Feature: Edit system configuration
  In order to edit system configuration
  As an admin
  I should be able to edit system configuration settings

  Background:
    Given a "default" catalog configuration

  @jira https://akeneo.atlassian.net/browse/PIM-6207
  Scenario: Does not display loading message by default
    Given I am logged in as "Peter"
    And I am on the System index page
    When I visit the "Loading messages" group
    And I fill in "loading_messages" with "They see me loadin', they hatin'"
    Then I should see the text "There are unsaved changes."
    When I save the configuration
    Then I should not see the text "There are unsaved changes."
