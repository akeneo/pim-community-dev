@javascript
Feature: Detach locale from channels
  In order to inactivate a locale completely
  As an administrator
  I need to be remove the locale from the channel

  Scenario: Detach a locale from all channels
    Given the "default" catalog configuration
    And I am logged in as "admin"
    And I set the "Armenian (Armenia), English (United States), French (France)" locales to the "ecommerce" channel
    And I am on the locales page
    And I filter by "Activated" with value "yes"
    Then the grid should contain 3 elements
    And I set the "English (United States), French (France)" locales to the "ecommerce" channel
    And I am on the locales page
    And I filter by "Activated" with value "yes"
    Then the grid should contain 2 elements
