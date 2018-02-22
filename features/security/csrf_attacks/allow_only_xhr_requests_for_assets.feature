Feature: Allow only XHR requests for some assets actions
  In order to protect assets from CSRF attacks
  As a developer
  I need to only do XHR calls for some assets actions

  Background:
    Given a "clothing" catalog configuration

  Scenario: Authorize only XHR calls for assets deletion
    When I make a direct authenticated DELETE call on the "paint" asset
    Then there should be a "paint" asset
