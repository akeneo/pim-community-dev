Feature: Allow only XHR requests for some rules actions
  In order to protect rules from CSRF attacks
  As a developer
  I need to only do XHR calls for some rules actions

  Background:
    Given a "clothing" catalog configuration
    And the following product rule definitions:
      """
      set_tees_description:
        priority: 10
        conditions:
          - field:    categories
            operator: IN
            value:
              - tees
        actions:
          - type:  set
            field: description
            value: an other description
            locale: fr_FR
            scope: tablet
      """

  Scenario: Authorize only XHR calls for rules deletion
    When I make a direct authenticated DELETE call on the "set_tees_description" rule
    Then there should be a "set_tees_description" rule
