@javascript
Feature: Compare fields and only see what I want
  In order to only see what I really need to work on my product
  As a user
  I need to be able to compare and copy values from different versions of the product

  Background:
    Given a "clothing" catalog configuration
    And the following product:
      | sku    | family | categories | name-fr_FR | description-en_US-mobile | description-fr_FR-mobile |
      | tshirt | tees   | tees       | Floup      | City tee                 | T-shirt de ville         |
    And the following product drafts:
      | product | status | author | result                                                                   |
      | tshirt  | draft  | Sandra | {"values":{"sku":[{"locale":null,"scope":null,"data":"My tshirt"}]}}     |
      | tshirt  | draft  | Mary   | {"values":{"manufacturer":[{"locale":null,"scope":null,"data":"nike"}]}} |

  Scenario: See localizable, scopable and changed fields if I compare my draft with a working copy
    Given I am logged in as "Mary"
    And I edit the "tshirt" product
    And I open the comparison panel
    And I switch the comparison source to "working_copy"
    Then I should see the comparison field "Manufacturer"
    And I should see the comparison field "Name"
    And I should not see the comparison field "SKU"

  Scenario: See changed fields if I compare my draft with an other draft
    Given I am logged in as "Mary"
    And I edit the "tshirt" product
    And I open the comparison panel
    And I switch the comparison source to "draft_of_Sandra"
    Then I should not see the comparison field "Manufacturer"
    And I should not see the comparison field "Name"
    And I should see the comparison field "SKU"

  Scenario: See changed fields if I compare my draft with my draft
    Given I am logged in as "Mary"
    And I edit the "tshirt" product
    And I open the comparison panel
    And I switch the comparison source to "my_draft"
    Then I should not see the comparison field "Manufacturer"
    And I should see the comparison field "Name"
    And I should see the comparison field "Description"
    And I should not see the comparison field "SKU"

  Scenario: See localizable and scopable fields if I compare the working copy with the working copy
    Given I am logged in as "Julia"
    And I edit the "tshirt" product
    And I open the comparison panel
    And I switch the comparison source to "working_copy"
    Then I should not see the comparison field "Manufacturer"
    And I should see the comparison field "Name"
    And I should see the comparison field "Description"

  Scenario: See ocalizable and scopable fields if I compare the working copy with a draft
    Given I am logged in as "Julia"
    And I edit the "tshirt" product
    And I open the comparison panel
    And I switch the comparison source to "draft_of_Mary"
    Then I should see the comparison field "Manufacturer"
    And I should not see the comparison field "SKU"
    And I should not see the comparison field "Name"
    Given I switch the comparison source to "draft_of_Sandra"
    Then I should not see the comparison field "Manufacturer"
    And I should see the comparison field "SKU"
    And I should not see the comparison field "Name"
