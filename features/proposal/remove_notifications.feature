@javascript
Feature: Remove notifications
  In order to easily quickly now if my proposals have been removed
  As a proposal redactor
  I need to be able to see a notification when the owner removes a proposal

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
    Given Mary started to propose the following change to "my-jacket":
      | field | value |
      | Name  | Coat  |
    And I am logged in as "Julia"
    And I edit the "my-jacket" product
    When I visit the "Proposals" tab
    And I click on the "remove" action of the row which contains "Name"

  Scenario: A notification is sent when I remove a proposal with comment from the product draft page
    And I fill in this comment in the popin: "You're fired."
    And I press the "Send" button in the popin
    And I logout
    And I am logged in as "Mary"
    And I am on the dashboard page
    Then I should have 1 new notification
    And I should see notification:
      | type  | message                                                         | comment       |
      | error | Julia Stark has deleted your proposal for the product my-jacket | You're fired. |
    When I click on the notification "Julia Stark has deleted your proposal for the product my-jacket"
    Then I should be on the product "my-jacket" edit page

  Scenario: A notification is sent when I remove a proposal from the product draft page
    And I press the "Send" button in the popin
    And I logout
    And I am logged in as "Mary"
    And I am on the dashboard page
    Then I should have 1 new notification
    And I should see notification:
      | type  | message                                                         |
      | error | Julia Stark has deleted your proposal for the product my-jacket |
    When I click on the notification "Julia Stark has deleted your proposal for the product my-jacket"
    Then I should be on the product "my-jacket" edit page
