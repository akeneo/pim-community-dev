Feature: Allow only XHR requests for some attribute groups actions
  In order to protect attribute groups from CSRF attacks
  As a developer
  I need to only do XHR calls for some attribute groups actions

  Background:
    Given a "footwear" catalog configuration
    And the following attribute group:
      | code      | label-en_US |
      | csrf_test | csrf_test   |

  Scenario: Authorize only XHR calls for attribute groups deletion
    When I make a direct authenticated DELETE call on the "csrf_test" attribute group
    Then there should be a "csrf_test" attribute group
