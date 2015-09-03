@javascript
Feature: Display links widget
  As a regular user
  I need to be redirected to see links in the dashboard
  In order to have a quick access of main sections of the PIM

  Scenario: Display links widget
    Given a "default" catalog configuration
    Given  I am logged in as "Mary"
    When I am on the dashboard page
    Then I should see "Manage Products"
    Then I should see "Manage Families"
    Then I should see "Manage Attributes"
    Then I should see "Manage Categories"
    When I follow "Manage Products"
    Then I should be on the products page
    When I am on the dashboard page
    And I follow "Manage Families"
    Then I should be on the families page
    When I am on the dashboard page
    And I follow "Manage Attributes"
    Then I should be on the attributes page
    When I am on the dashboard page
    And I follow "Manage Categories"
    Then I should be on the categories page
