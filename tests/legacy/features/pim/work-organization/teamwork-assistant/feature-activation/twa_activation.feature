@javascript
Feature: Teamwork Assistant is not available when the feature is disabled

  Background:
    Given the "default" catalog configuration
    And I am logged in as "Julia"

  Scenario: Teamwork Assistant is not available when deactivated
    When I am on the dashboard page
    Then I should see the text "Last operations"
    Then I should not see the text "Projects"
    When I am on the products grid
    Then I should not see the "Projects" view type
