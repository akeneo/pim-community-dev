@javascript
Feature: Display localized numbers in proposals
  In order to have complete localized UI
  As a product owner
  I need to be able to show localized numbers in the proposals list

  Background:
    Given an "clothing" catalog configuration
    And the following attributes:
      | code           | label          | type   | decimals_allowed | group | default_metric_unit | metric_family |
      | decimal_number | decimal_number | number | yes              | info  |                     |               |
      | weight         | Weight         | metric | yes              | info  | KILOGRAM            | Weight        |
    And the following product category accesses:
      | product category | user group | access |
      | 2014_collection  | Redactor   | edit   |
    And the following products:
      | sku     | family | categories      |
      | tshirt  | pants  | 2014_collection |
    And the following product drafts:
      | product | status | author | result                                                                                                                                                                                                                                                          |
      | tshirt  | ready  | Mary   | {"values":{"decimal_number":[{"locale":"en_US","scope":null,"data":"98.765"}], "weight":[{"locale":"en_US","scope":null,"data":{"data":"12.1234", "unit":"KILOGRAM"}}], "price":[{"locale":"en_US","scope":null,"data":[{"data":"15.25", "currency":"USD"}]}]}} |

  Scenario: Successfully display localized attributes of a proposal in the french format
    And I am logged in as "Julia"
    And I edit my profile
    And I visit the "Interfaces" tab
    And I fill in the following information:
      | Ui locale | French (France) |
    And I save the user
    And I am on the "tshirt" product page
    When I visit the "Propositions" tab
    Then I should see "15,25 $US"
    And I should see "12,1234 KILOGRAM"
    And I should see "98,765"
