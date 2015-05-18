Feature: Approve draft
  In order to update products
  As a redactor user
  I need to be able to approve a draft

  Background:
    Given a "clothing" catalog configuration
    And the following product:
      | sku           | categories |
      | akeneo_tshirt | tshirts    |
      | akeneo_sweat  | tshirts    |
    And I should get the following products after apply the following updater to it:
      | product       | actions                                                                               | result | username |
      | akeneo_tshirt | [{"type": "set_data", "field": "handmade", "data": 1, "locale": null, "scope": null}] | {}     | Mary     |

  Scenario: Successfully approve a draft
    Given I approve the proposal of the product "akeneo_tshirt" created by user "Mary"

  Scenario: Failed to approve a draft with a not found user
    Given I failed to approve the proposal of the product "akeneo_tshirt" created by user "Julia"

  Scenario: Failed to approve a draft with a not found product
    Given I failed to approve the proposal of the product "akeneo_sweat" created by user "Mary"
