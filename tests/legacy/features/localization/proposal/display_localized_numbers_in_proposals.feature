@javascript
Feature: Display localized numbers in proposals
  In order to have complete localized UI
  As a product owner
  I need to be able to show localized numbers in the proposals list

  Background:
    Given an "clothing" catalog configuration
    And the following attributes:
      | code           | label-en_US    | type               | decimals_allowed | negative_allowed | group | default_metric_unit | metric_family |
      | decimal_number | decimal_number | pim_catalog_number | 1                | 0                | info  |                     |               |
      | weight         | Weight         | pim_catalog_metric | 1                | 0                | info  | KILOGRAM            | Weight        |
    And the following family:
      | code       | attributes                           |
      | high_heels | sku,name,price,decimal_number,weight |
    And the following product category accesses:
      | product category | user group | access |
      | 2014_collection  | Redactor   | edit   |
      | 2014_collection  | Manager    | own    |
    And the following products:
      | sku    | family     | categories      |
      | tshirt | high_heels | 2014_collection |
    Given Mary proposed the following change to "tshirt":
      | tab                 | field          | value            |
      | Product information | decimal_number | 98.765           |
      | Product information | Weight         | 12.1234 Kilogram |
      | Marketing           | Price          | 15.25 USD        |

  Scenario: Successfully display localized attributes of a proposal in the french format
    Given I am logged in as "Julia"
    And I am on the User profile edit page
    And I visit the "Interfaces" tab
    And I fill in the following information:
      | UI locale | French (France) |
    And I save the user
    And I should not see the text "There are unsaved changes"
    And I am on the "tshirt" product page
    When I visit the "Propositions" column tab
    Then I should see the text "15,25 $US"
    And I should see the text "12,1234 KILOGRAM"
    And I should see the text "98,765"
