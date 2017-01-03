@javascript
Feature: Browse the history in the history tab of the pinbar
  In order to easily know what pages were visited
  As a regular user
  I need to be able to see navigation history

  Background:
    Given a "default" catalog configuration
    And the following products:
      | sku       |
      | pineapple |
    And I am logged in as "Mary"

  @jira https://akeneo.atlassian.net/browse/PIM-5828
  Scenario: Add pages to the pinbar
    Given I am on the "pineapple" product page
    And I click back to grid
    And I am on the family index page
    And I am on the home page
    When I click on the pin bar dot menu
    And I press the "History" button
    Then I should see the text "Products pineapple | Edit"
