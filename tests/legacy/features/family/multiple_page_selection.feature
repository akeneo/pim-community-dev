@javascript
Feature: Select items on several pages
  In order to enrich several families at once
  As a product manager
  I need to be able to select them on multiple pages

  Background:
    Given the "default" catalog configuration
    And I am logged in as "Julia"

  @jira https://akeneo.atlassian.net/browse/PIM-7214
  Scenario: Select multiple families on multiple pages
    Given 30 empty families
    When I am on the families page
    And I sort by "Label" value ascending
    And I select rows [family_1]
    And I select all visible entities
    Then I should see the text "25 selected families"
    When I unselect rows [family_1]
    Then I should see the text "24 selected families"
    When I follow "No. 2"
    And I select rows [family_9]
    Then I should see the text "25 selected families"
    When I select all visible entities
    Then I should see the text "29 selected families"
    When I follow "No. 1"
    And I unselect rows [family_2]
    Then I should see the text "28 selected families"
