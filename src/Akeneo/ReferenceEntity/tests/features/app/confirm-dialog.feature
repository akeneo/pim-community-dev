Feature: Edit a reference entity
  In order to update the information of a reference entity
  As a user
  I want see the details of a reference entity and update them

  Background:
    Given a valid reference entity

#  @acceptance-front
  Scenario: Display confirmation dialog when the user reload the page and accept it
    When the user changes the reference entity "designer" with:
      | labels | {"en_US": "Stylist", "fr_FR": "Styliste"} |
    Then the user wants to see the confirmation dialog before leaving the page and accept
    And the user reload the page

#  @acceptance-front
  Scenario: Display confirmation dialog when the user reload the page and dismiss it
    When the user changes the reference entity "designer" with:
      | labels | {"en_US": "Stylist", "fr_FR": "Styliste"} |
    Then the user wants to see the confirmation dialog before leaving the page and dismiss
    And the user reload the page
    And the user should be notified that modification have been made

#  @acceptance-front
  Scenario: Display confirmation dialog when the user goes on another page and accept it
    When the user changes the reference entity "designer" with:
      | labels | {"en_US": "Stylist", "fr_FR": "Styliste"} |
    Then the user wants to see the confirmation dialog before leaving the page and accept
    And the user goes to "http://www.pim-test.com"

#  @acceptance-front
  Scenario: Display confirmation dialog when the user goes on another page and dismiss it
    When the user changes the reference entity "designer" with:
      | labels | {"en_US": "Stylist", "fr_FR": "Styliste"} |
    Then the user wants to see the confirmation dialog before leaving the page and dismiss
    And the user goes to "http://www.pim-test.com"
    And the user should be notified that modification have been made
