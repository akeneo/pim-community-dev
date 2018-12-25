@acceptance-back
Feature: Automatically deactivate Franklin Insights
  In order to correctly use Franklin Insights
  As a product manager
  I want to Franklin Insights to be automatically deactivated if needed

  Scenario: Franklin Insights is automatically deactivated when all english locales are deactivated
    Given the following locales "fr_FR, en_US"
    And the following "ecommerce" channel with locales "fr_FR, en_US"
    And Franklin is configured with a valid token
    When a product manager deactivates all english locales
    Then Franklin is not activated
