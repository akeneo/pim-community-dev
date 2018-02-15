Feature: Allow only XHR requests for some category trees actions
  In order to protect category trees from CSRF attacks
  As a developer
  I need to only do XHR calls for some category trees actions

  Background:
    Given a "footwear" catalog configuration

  Scenario: Authorize only XHR calls for category trees move
    When I make a direct authenticated POST call to move the "sandals" category into the "winter_collection" category
    Then the category "sandals" should have "summer_collection" as parent

  Scenario: Authorize only XHR calls for category deletion
    When I make a direct authenticated DELETE call on the "sandals" category
    Then there should be a "sandals" category
