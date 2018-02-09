Feature: Detach locale from channels
  In order to inactivate a locale completely
  As an administrator
  I need to be remove the locale from the channel

#  Scenario: Detach a locale from all channels
#    Given the "default" catalog configuration
#    And I am logged in as "admin"
#    And I set the "Armenian (Armenia), English (United States), French (France)" locales to the "ecommerce" channel
#    And I am on the locales page
#    And I filter by "activated" with operator "" and value "yes"
#    Then the grid should contain 3 elements
#    And I set the "English (United States), French (France)" locales to the "ecommerce" channel
#    And I am on the locales page
#    And I filter by "activated" with operator "" and value "yes"
#    Then the grid should contain 2 elements

  @acceptance
  Scenario: When a locale is added to a channel it becomes enabled
    Given the following locales "fr_FR, en_US, de_DE"
    And the following "ecommerce" channel with locales "fr_FR"
    When I add the locale "en_US" from the "ecommerce" channel
    Then I should have activated locales "fr_FR,en_US"

  @acceptance
  Scenario: When a locale is removed from a channel it becomes disabled
    Given the following locales "fr_FR, en_US, de_DE"
    And the following "ecommerce" channel with locales "fr_FR,en_US"
    When I remove the locale "fr_FR" from the "ecommerce" channel
    Then I should have activated locales "en_US"
