@javascript
Feature: Does not display an happy message during loading screen
  In order to see the default message when I log in
  As a regular user
  I would not like to see a nice message by default

  Scenario: Does not display loading message by default
    Given a "default" catalog configuration
    When I am logged in as "Mary"
    Then I should not see a nice loading message
