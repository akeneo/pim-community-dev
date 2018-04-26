Feature: Allow only XHR requests for some attributes actions
  In order to protect attributes from CSRF attacks
  As a developer
  I need to only do XHR calls for some attributes actions

  Background:
    Given a "footwear" catalog configuration

  Scenario: Authorize only XHR calls for attributes deletion
    When I make a direct authenticated DELETE call on the "cap_color" attribute
    Then there should be a "cap_color" attribute
