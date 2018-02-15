Feature: Allow only XHR requests for some groups actions
  In order to protect groups from CSRF attacks
  As a developer
  I need to only do XHR calls for some groups actions

  Background:
    Given a "footwear" catalog configuration

  Scenario: Authorize only XHR calls for groups deletion
    When I make a direct authenticated DELETE call on the "similar_boots" group
    Then there should be a "similar_boots" group
