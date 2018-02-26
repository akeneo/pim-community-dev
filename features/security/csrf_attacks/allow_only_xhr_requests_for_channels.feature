Feature: Allow only XHR requests for some channels actions
  In order to protect channels from CSRF attacks
  As a developer
  I need to only do XHR calls for some channels actions

  Background:
    Given a "default" catalog configuration

  Scenario: Authorize only XHR calls for channels deletion
    When I make a direct authenticated DELETE call on the "mobile" channel
    Then there should be a "mobile" channel
