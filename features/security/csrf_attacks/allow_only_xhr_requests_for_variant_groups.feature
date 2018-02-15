Feature: Allow only XHR requests for some variant groups actions
  In order to protect variant groups from CSRF attacks
  As a developer
  I need to only do XHR calls for some variant groups actions

  Background:
    Given a "footwear" catalog configuration

  Scenario: Authorize only XHR calls for variant groups update
    When I make a direct authenticated POST call on the "caterpillar_boots" variant group to change its "en_US" label to "csrf"
    Then the label of variant group "caterpillar_boots" should be "Caterpillar boots"

  Scenario: Authorize only XHR calls for variant groups deletion
    When I make a direct authenticated DELETE call on the "caterpillar_boots" variant group
    Then there should be a "caterpillar_boots" variant group
