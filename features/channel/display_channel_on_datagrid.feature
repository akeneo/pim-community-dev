@javascript
Feature: Display channels on the datagrid
  In order to display existing channels
  As a user
  I need to be able to display available channels on the datagrid

  Background:
    Given a "footwear" catalog configuration

  Scenario: Display code of channel if no translation is available for the UI language
    Given I am logged in as "Julia"
    When I am on the products page
    Then I should see the text "Tablet"
    When I am on the my account page
    And I press the "Edit" button
    And I visit the "Interfaces" tab
    And I fill in the following information:
      | UI locale | Spanish (Mexico) |
    And I press the "Save" button
    And I am on the products page
    Then I should see the text "[tablet]"
