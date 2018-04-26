Feature: Allow only XHR requests for some users actions
  In order to protect users from CSRF attacks
  As a developer
  I need to only do XHR calls for some users actions

  Background:
    Given a "default" catalog configuration

  Scenario: Authorize only XHR calls for users deletion
    When I make a direct authenticated DELETE call on the "Mary" user
    Then there should be a "Mary" user
