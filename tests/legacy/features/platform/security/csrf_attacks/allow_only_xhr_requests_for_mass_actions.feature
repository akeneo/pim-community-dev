Feature: Allow only XHR requests for some mass actions
  In order to protect mass actions from CSRF attacks
  As a developer
  I need to only do XHR calls for some mass actions

  Background:
    Given a "default" catalog configuration
    And the following product:
      | sku        |
      | high-heels |

  Scenario: Authorize only XHR calls for mass actions
    When I make a direct authenticated GET call to mass delete "high-heels" product
    Then there should be a "high-heels" product
