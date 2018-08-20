Feature: Allow only XHR requests for some group types actions
  In order to protect group types from CSRF attacks
  As a developer
  I need to only do XHR calls for some group types actions

  Background:
    Given a "footwear" catalog configuration

  Scenario: Authorize only XHR calls for group types deletion
    When I make a direct authenticated DELETE call on the "XSELL" group type
    Then there should be a "XSELL" group type
