Feature: Create a draft with a text fields
  In order to update products
  As a redactor user
  I need to be able to create a draft with a text field

  Background:
    Given a "clothing" catalog configuration
    And the following product:
      | sku           | categories |
      | akeneo_tshirt | tshirts    |

  Scenario: Successfully add a draft without add attribute in product
    Given I should get the following products after apply the following updater to it:
      | product       | actions                                                                                                | result | username |
      | akeneo_tshirt | [{"type": "set_data", "field": "description", "data": "Tshirt Akeneo", "locale": null, "scope": null}] | {}     | Mary     |
    And I should get the following proposals:
      | product        | username | result                                                                                   |
      | akeneo_tshirt  | Mary     | {"values": {"description": [{"locale": null, "scope": null, "value": "Tshirt Akeneo"}]}} |
    And the product "akeneo_tshirt" should have the following values:
      | description | |

  Scenario: Successfully add a draft without update attribute in product
    Given the following product values:
      | product       | attribute | value |
      | akeneo_tshirt | description    | "Tshirt Akeneo"     |
    Then I should get the following products after apply the following updater to it:
      | product       | actions                                                                                                          | result | username |
      | akeneo_tshirt | [{"type": "set_data", "field": "description", "data": "Wonderful Akeneo Tshirt", "locale": null, "scope": null}] | {}     | Mary     |
    And I should get the following proposals:
      | product        | username | result                                                                                             |
      | akeneo_tshirt  | Mary     | {"values": {"description": [{"locale": null, "scope": null, "value": "Wonderful Akeneo Tshirt"}]}} |
    And the product "akeneo_tshirt" should have the following values:
      | description | "Tshirt Akeneo" |

  Scenario: Do not create a draft with same values as product
    Given the following product values:
      | product       | attribute   | value           |
      | akeneo_tshirt | description | "Tshirt Akeneo" |
    Then I should get the following products after apply the following updater to it:
      | product       | actions                                                                                                | result | username |
      | akeneo_tshirt | [{"type": "set_data", "field": "description", "data": "Tshirt Akeneo", "locale": null, "scope": null}] | {}     | Mary     |
    And I should not get the following proposal:
      | product       | username |
      | akeneo_tshirt | Mary     |
