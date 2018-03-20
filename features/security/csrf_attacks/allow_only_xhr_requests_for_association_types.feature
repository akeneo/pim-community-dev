Feature: Allow only XHR requests for some association types actions
  In order to protect association types from CSRF attacks
  As a developer
  I need to only do XHR calls for some association types actions

  Background:
    Given a "default" catalog configuration

  Scenario: Authorize only XHR calls for association types deletion
    When I make a direct authenticated DELETE call on the "PACK" association type
    Then there should be a "PACK" association type
