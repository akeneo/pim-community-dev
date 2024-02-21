@javascript
Feature: Allow only XHR requests for some comments actions
  In order to protect comments from CSRF attacks
  As a developer
  I need to only do XHR calls for some comments actions

  Background:
    Given a "default" catalog configuration
    And the following product:
      | sku        |
      | high-heels |
    And the following product comments:
      | product    | # | author | message                                                        | parent | created_at |
      | high-heels | 1 | Julia  | Waiting for the confirmation of our manufacturer to update it. | 0      | 29-Aug-14  |

  Scenario: Authorize only XHR calls for comments deletion
    When I make a direct authenticated DELETE call on the last comment of "high-heels" product
    And I am logged in as "Julia"
    And I am on the "high-heels" product page
    And I visit the "Comments" column tab
    Then I should see the text "Waiting for the confirmation of our manufacturer to update it."
