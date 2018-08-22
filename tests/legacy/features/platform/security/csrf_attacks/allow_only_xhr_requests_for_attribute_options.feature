Feature: Allow only XHR requests for some attribute options actions
  In order to protect attribute options from CSRF attacks
  As a developer
  I need to only do XHR calls for some attribute options actions

  Background:
    Given a "footwear" catalog configuration

  Scenario: Authorize only XHR calls for attribute options creation
    When I make a direct authenticated POST call to create a "csrf_test" attribute option for attribute "color"
    Then there should not be a "csrf_test" attribute option for attribute "color"

  Scenario: Authorize only XHR calls for attribute options update
    When I make a direct authenticated PUT call to update the "white" attribute option for attribute "color"
    Then there should be a "white" attribute option for attribute "color"

  Scenario: Authorize only XHR calls for attribute options deletion
    When I make a direct authenticated DELETE call on the "white" attribute option for attribute "color"
    Then there should be a "white" attribute option for attribute "color"

  Scenario: Authorize only XHR calls for attribute options sorting
    When I make a direct authenticated PUT call to sort attribute options of attribute "color"
    Then the order for attribute options "white" of attribute "color" should be 1
