Feature: Allow only XHR requests for some variant group attributes actions
  In order to protect variant group attributes from CSRF attacks
  As a developer
  I need to only do XHR calls for some variant group attributes actions

  Background:
    Given a "footwear" catalog configuration

  Scenario: Authorize only XHR calls for variant group attributes deletion
    Given I add the attribute "comment" with value "This is a comment." to the "caterpillar_boots" variant group
    When I make a direct authenticated DELETE call on the "comment" attribute of the "caterpillar_boots" variant group
    Then the variant group "caterpillar_boots" should have the "comment" attribute
