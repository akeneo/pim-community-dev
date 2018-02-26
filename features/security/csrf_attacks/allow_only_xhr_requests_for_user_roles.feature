Feature: Allow only XHR requests for some user roles actions
  In order to protect user roles from CSRF attacks
  As a developer
  I need to only do XHR calls for some user roles actions

  Background:
    Given a "default" catalog configuration

  Scenario: Authorize only XHR calls for user roles deletion
    When I make a direct authenticated DELETE call on the "ROLE_USER" user role
    Then there should be a "User" user role
