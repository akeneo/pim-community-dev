@javascript
Feature: Select items on several pages
  In order to enrich several products at once
  As a product manager
  I need to be able to select them on multiple pages

  Background:
    Given the "default" catalog configuration
    And I am logged in as "Julia"

  @jira https://akeneo.atlassian.net/browse/PIM-7214
  Scenario: Select multiple products on multiple pages
    Given 30 empty products
    When I am on the products page
    And I sort by "ID" value ascending
    And I select row product_1
    And I select all visible entities
    Then I should see the text "25 selected results"
    When I unselect row product_1
    Then I should see the text "24 selected results"
    When I follow "No. 2"
    And I select row product_9
    Then I should see the text "25 selected results"
    When I select all visible entities
    Then I should see the text "29 selected results"
    When I follow "No. 1"
    And I unselect row product_11
    Then I should see the text "28 selected results"
