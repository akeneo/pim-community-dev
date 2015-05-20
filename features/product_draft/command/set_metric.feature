Feature: Create a draft with a metric fields
  In order to update products
  As a redactor user
  I need to be able to create a draft with a metric field

  Background:
    Given a "clothing" catalog configuration
    And the following product:
      | sku           | categories |
      | akeneo_tshirt | tshirts    |

  Scenario: Successfully add a draft without add attribute in product
    Given I should get the following products after apply the following updater to it:
      | product       | actions                                                                              | result | username |
      | akeneo_tshirt | [{"type": "set_data", "field": "length", "data": 12, "locale": null, "scope": null}] | {}     | Mary     |
    And I should get the following proposals:
      | product        | username | result                                                                 |
      | akeneo_tshirt  | Mary     | {"values": {"length": [{"locale": null, "scope": null, "value": 12}]}} |
    And the product "akeneo_tshirt" should have the following values:
      | length | |

  Scenario: Successfully add a draft without update attribute in product
    Given the following product values:
      | product       | attribute | value |
      | akeneo_tshirt | length    | 15    |
    Then I should get the following products after apply the following updater to it:
      | product       | actions                                                                              | result | username |
      | akeneo_tshirt | [{"type": "set_data", "field": "length", "data": 12, "locale": null, "scope": null}] | {}     | Mary     |
    And I should get the following proposals:
      | product        | username | result                                                                 |
      | akeneo_tshirt  | Mary     | {"values": {"length": [{"locale": null, "scope": null, "value": 12}]}} |
    And the product "akeneo_tshirt" should have the following values:
      | length | 15 |

  Scenario: Do not create a draft with same values as product
    Given the following product values:
      | product       | attribute | value |
      | akeneo_tshirt | length    | 20    |
    Then I should get the following products after apply the following updater to it:
      | product       | actions                                                                              | result | username |
      | akeneo_tshirt | [{"type": "set_data", "field": "length", "data": 20, "locale": null, "scope": null}] | {}     | Mary     |
    And I should not get the following proposal:
      | product       | username |
      | akeneo_tshirt | Mary     |
