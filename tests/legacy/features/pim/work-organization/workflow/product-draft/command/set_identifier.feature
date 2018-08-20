Feature: Create a draft with a identifier fields
  In order to update products
  As a redactor user
  I need to be able to create a draft with a identifier field

  Background:
    Given a "clothing" catalog configuration
    And the following product:
      | sku           | categories |
      | akeneo_tshirt | tops       |

  Scenario: Successfully add a draft without add attribute in product
    Given I should get the following product drafts after apply the following updater to it:
      | product       | actions                                                                                           | result | username |
      | akeneo_tshirt | [{"type": "set_data", "field": "sku", "data": "akeneo_tshirt_v2", "locale": null, "scope": null}] | {}     | Mary     |
    And I should get the following proposals:
      | product       | username | result                                                                                                                                                               |
      | akeneo_tshirt | Mary     | {"values": {"sku": [{"locale": null, "scope": null, "data": "akeneo_tshirt_v2"}]}, "review_statuses": {"sku": [{"locale": null, "scope": null, "status": "draft"}]}} |
    And the product "akeneo_tshirt" should have the following values:
      | sku | akeneo_tshirt |

  Scenario: Successfully add a draft without update attribute in product
    Given I should get the following product drafts after apply the following updater to it:
      | product       | actions                                                                                           | result | username |
      | akeneo_tshirt | [{"type": "set_data", "field": "sku", "data": "akeneo_tshirt_v2", "locale": null, "scope": null}] | {}     | Mary     |
    And I should get the following proposals:
      | product       | username | result                                                                                                                                                               |
      | akeneo_tshirt | Mary     | {"values": {"sku": [{"locale": null, "scope": null, "data": "akeneo_tshirt_v2"}]}, "review_statuses": {"sku": [{"locale": null, "scope": null, "status": "draft"}]}} |
    And the product "akeneo_tshirt" should have the following values:
      | sku | akeneo_tshirt |

  Scenario: Do not create a draft with same values as product
    Given I should get the following product drafts after apply the following updater to it:
      | product       | actions                                                                                        | result | username |
      | akeneo_tshirt | [{"type": "set_data", "field": "sku", "data": "akeneo_tshirt", "locale": null, "scope": null}] | {}     | Mary     |
    And I should not get the following proposal:
      | product       | username |
      | akeneo_tshirt | Mary     |
