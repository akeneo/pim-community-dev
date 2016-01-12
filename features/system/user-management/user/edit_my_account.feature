@javascript
Feature: Change my profile
  In order to change my profile
  As an administrator
  I need to be able to change my profile informations

  Background:
    Given the "apparel" catalog configuration
    And I am logged in as "Peter"

  Scenario: Successfully change avatar
    Given I edit the "admin" user
    When I attach file "akeneo.jpg" to "Avatar"
    And I save the user
    Then I should see "User saved"
    And I should not see the default avatar

  Scenario: Successfully generate API key
    Given I am on the my account page
    Then The API key should be Peter_api_key
    When I press the "btn-apigen" button
    Then The API key should not be Peter_api_key
