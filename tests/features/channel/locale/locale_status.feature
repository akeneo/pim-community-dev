Feature: Manage the locale status
  In order to (in)activate a locale
  As an administrator
  I need to be add or remove the locale from the channel

  @acceptance-back
  Scenario: By default a locale is disabled by when it is added to a channel it becomes enabled
    Given the following locales "fr_FR, en_US"
    And the following "ecommerce" channel with locales "fr_FR"
    Then the locale "fr_FR" should be activated
    And the locale "en_US" should be deactivated
    When the locale "en_US" is added to the "ecommerce" channel
    Then the locales "fr_FR,en_US" should be activated

  @acceptance-back
  Scenario: When a locale is removed from a channel it becomes disabled
    Given the following locales "fr_FR, en_US"
    And the following "ecommerce" channel with locales "fr_FR, en_US"
    When the locale "fr_FR" is removed from the "ecommerce" channel
    Then the locale "en_US" should be activated
    And the locale "fr_FR" should be deactivated
