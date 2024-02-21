Feature: Allow only XHR requests for some notifications actions
  In order to protect notifications from CSRF attacks
  As a developer
  I need to only do XHR calls for some notifications actions

  Background:
    Given a "default" catalog configuration

  Scenario: Authorize only XHR calls for export notifications deletion
    Given there is a notification for user "Julia"
    When I make a direct authenticated DELETE call on the last notification of user "Julia"
    Then there should be 1 notification for user "Julia"
