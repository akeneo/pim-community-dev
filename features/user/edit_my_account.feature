@javascript
Feature: Change my profile picture
  In order to change my profile picture
  As an administrator
  I need to be able to change my profile picture

  Background:
    Given the "apparel" catalog configuration
    And I am logged in as "Peter"

  @javascript
  Scenario: Successfully change avatar
    Given I edit the "admin" user
    When I attach file "akeneo.jpg" to "Avatar"
    And I save the user
    Then I should see "User saved"
    And I should not see the default avatar
