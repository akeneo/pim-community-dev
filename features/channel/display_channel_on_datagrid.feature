@javascript
Feature: Display channels on the datagrid
  In order to display existing channels
  As a user
  I need to be able to display available channels on the datagrid

  Background:
    Given a "footwear" catalog configuration
    And the following locale accesses:
      | locale | user group | access |
      | es_MX  | All        | view   |

  Scenario: Display code of channel if no translation is available for the UI language
    Given I am logged in as "Julia"
    When I am on the products page
    Then I should see the text "Tablet"
    When I am on the "tablet" channel page
    And I change the "Locales" to "English (United States), Spanish (Mexico)"
    And I press the "Save" button
    Then I should not see the text "There are unsaved changes."
    When I am on the products page
    And I switch the locale to "es_MX"
    Then I should see the text "[tablet]"
