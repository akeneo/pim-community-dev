Feature: Display proposal widget
  In order to easily see which products have pending proposals
  As Julia
  I need to be able to see a widget with pending proposals on the dashboard

  Scenario: Display proposal widget
    Given the "default" catalog configuration
    And I am logged in as "Julia"
    When I am on the dashboard page
    Then I should see "Proposals to review"
    And I should see "No proposals to review"
