@javascript
Feature: Be notified on draft submission
  In order to control which data should be applied to a product
  As a product manager
  I need to be notified when someone send a proposal on one of my products

  Background:
    Given a "clothing" catalog configuration
    And the product:
      | family                    | jackets           |
      | categories                | winter_top        |
      | sku                       | my-jacket         |
      | name-en_US                | Jacket            |
      | description-en_US-mobile  | An awesome jacket |
      | number_in_stock-mobile    | 4                 |
      | number_in_stock-tablet    | 20                |
      | price                     | 45 USD            |
      | manufacturer              | Volcom            |
      | weather_conditions        | dry, wet          |
      | handmade                  | 0                 |
      | release_date-mobile       | 2014-05-14        |
      | length                    | 60 CENTIMETER     |
      | legacy_attribute          | legacy            |
      | datasheet                 |                   |
      | side_view                 |                   |

  Scenario: Successfully be notified when someone sends a proposal for approval
    Given Mary proposed the following change to "my-jacket":
      | field | value       |
      | SKU   | your-jacket |
    And I am logged in as "Julia"
    And I am on the dashboard page
    Then I should have 1 new notification
    And I should see notification:
      | type | message                                                         |
      | add  | Mary Smith has sent a proposal to review for the product Jacket |
    When I click on the notification "Mary Smith has sent a proposal to review for the product Jacket"
    Then I should be on the product "my-jacket" edit page
    And I should see the columns Author, Changes, Proposed at and Status
    And the grid should contain 1 element

  Scenario: Successfully be notified when someone sends a proposal for approval with a comment
    Given Mary proposed the following change to "my-jacket" with the comment "Please approve this fast.":
      | field | value       |
      | SKU   | your-jacket |
    And I am logged in as "Julia"
    And I am on the dashboard page
    Then I should have 1 new notification
    And I should see notification:
      | type | message                                                         | comment                   |
      | add  | Mary Smith has sent a proposal to review for the product Jacket | Please approve this fast. |
    When I click on the notification "Mary Smith has sent a proposal to review for the product Jacket"
    Then I should be on the product "my-jacket" edit page
    And I should see the columns Author, Changes, Proposed at and Status
    And the grid should contain 1 element
