@javascript
Feature: Adapt notification for redactor depending on action on their proposal
  In order to ease the cleaning of proposals
  As a product manager
  I need to be able to delete all values I can edit on a product draft

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Julia"
    And I am on the "hoodies" family page
    And I visit the "Attributes" tab
    And I add available attribute Weather conditions
    And I save the family
    And I logout
    And the following attribute group accesses:
      | attribute group | user group | access |
      | sizes           | Manager    | view   |
      | sizes           | Redactor   | edit   |
    And the following products:
      | sku  | family  | categories | name-en_US               | weather_conditions |
      | sp-1 | hoodies | tops       | South Park Hoodie - Timm | dry                |

  Scenario: When I reject more than 3 values, it shows only number of attributes rejected
    Given Mary proposed the following change to "sp-1":
      | field              | value                     | locale | scope  | tab                 |
      | Name               | South Park Hoodie - Timmy | en_US  |        | Product information |
      | Description        | Timmy!!!                  | en_US  | mobile | Product information |
      | Weather conditions | Dry, Cold                 |        |        | Product information |
      | Manufacturer       | Volcom                    |        |        | Product information |
      | Size               | M                         |        |        | Sizes               |
    And I am logged in as "Julia"
    And I am on the proposals page
    And I click on the "Reject all" action of the row which contains "South Park Hoodie - Timm"
    And I press the "Send" button in the popin
    And I logout
    And I am logged in as "Mary"
    When I am on the dashboard page
    Then I should have 1 new notification
    And I should see notification:
      | type  | message                                                                                   |
      | error | Julia Stark has rejected values for 4 attributes for the product South Park Hoodie - Timm |

  Scenario: When I accept more than 3 values, it shows only number of attributes accepted
    Given Mary proposed the following change to "sp-1":
      | field              | value                     | locale | scope  | tab                 |
      | Name               | South Park Hoodie - Timmy | en_US  |        | Product information |
      | Description        | Timmy!!!                  | en_US  | mobile | Product information |
      | Weather conditions | Dry, Cold                 |        |        | Product information |
      | Manufacturer       | Volcom                    |        |        | Product information |
      | Size               | M                         |        |        | Sizes               |
    And I am logged in as "Julia"
    And I am on the proposals page
    And I click on the "Approve all" action of the row which contains "South Park Hoodie - Timm"
    And I press the "Send" button in the popin
    And I logout
    And I am logged in as "Mary"
    When I am on the dashboard page
    Then I should have 1 new notification
    And I should see notification:
      | type    | message                                                                                    |
      | success | Julia Stark has accepted values for 4 attributes for the product South Park Hoodie - Timmy |

  Scenario: When I delete more than 3 values, it shows only number of attributes deleted
    Given Mary started to propose the following change to "sp-1":
      | field              | value                     | locale | scope  | tab                 |
      | Name               | South Park Hoodie - Timmy | en_US  |        | Product information |
      | Description        | Timmy!!!                  | en_US  | mobile | Product information |
      | Weather conditions | Dry, Cold                 |        |        | Product information |
      | Manufacturer       | Volcom                    |        |        | Product information |
      | Size               | M                         |        |        | Sizes               |
    And I am logged in as "Julia"
    And I edit the "sp-1" product
    And I visit the "Proposals" column tab
    And I click on the "remove" action of the row which contains "Mary"
    And I press the "Send" button in the popin
    And I logout
    And I am logged in as "Mary"
    When I am on the dashboard page
    Then I should have 1 new notification
    And I should see notification:
      | type  | message                                                                                  |
      | error | Julia Stark has deleted values for 4 attributes for the product South Park Hoodie - Timm |
