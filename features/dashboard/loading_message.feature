@javascript
Feature: Display an happy message during loading screen
  In order to feel better when I log in
  As a regular user
  I would like to see a nice message

  Scenario: Display loading message
    Given a "default" catalog configuration
    When I am logged in as "Mary"
    Then I should see a nice loading message
