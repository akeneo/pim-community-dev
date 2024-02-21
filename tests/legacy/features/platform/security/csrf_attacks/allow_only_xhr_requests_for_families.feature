Feature: Allow only XHR requests for some families actions
  In order to protect families from CSRF attacks
  As a developer
  I need to only do XHR calls for some families actions

  Background:
    Given a "footwear" catalog configuration

  Scenario: Authorize only XHR calls for families deletion
    When I make a direct authenticated DELETE call on the "boots" family
    Then there should be a "boots" family
