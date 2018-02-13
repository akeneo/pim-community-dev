Feature: Allow only XHR requests for some user groups actions
  In order to protect user groups from CSRF attacks
  As a developer
  I need to only do XHR calls for some user groups actions

  Background:
    Given a "default" catalog configuration

  Scenario: Authorize only XHR calls for user groups deletion
    When I make a direct authenticated DELETE call on the "Redactor" user group
    Then there should be a "Redactor" user group
