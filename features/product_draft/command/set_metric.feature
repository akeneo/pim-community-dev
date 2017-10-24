Feature: Create a draft with a metric fields
  In order to update products
  As a redactor user
  I need to be able to create a draft with a metric field

  Background:
    Given a "clothing" catalog configuration
    And the following product:
      | sku           | categories |
      | akeneo_tshirt | tops       |

  Scenario: Successfully add a draft without add attribute in product
    Given I should get the following product drafts after apply the following updater to it:
      | product       | actions                                                                                                                     | result | username |
      | akeneo_tshirt | [{"type": "set_data", "field": "length", "data": {"amount": 12.0000, "unit": "CENTIMETER"}, "locale": null, "scope": null}] | {}     | Mary     |
    And I should get the following proposals:
      | product       | username | result                                                                                                                                                                                            |
      | akeneo_tshirt | Mary     | {"values": {"length": [{"locale": null, "scope": null, "data": {"amount":"12.0000", "unit":"CENTIMETER"}}]}, "review_statuses": {"length": [{"locale": null, "scope": null, "status": "draft"}]}} |
    And the product "akeneo_tshirt" should have the following values:
      | length |  |

  Scenario: Successfully add a draft without update attribute in product
    Given the following product values:
      | product       | attribute | value              |
      | akeneo_tshirt | length    | 15.0000 CENTIMETER |
    Then I should get the following product drafts after apply the following updater to it:
      | product       | actions                                                                                                                   | result | username |
      | akeneo_tshirt | [{"type": "set_data", "field": "length", "data": {"amount":12.0000, "unit":"CENTIMETER"}, "locale": null, "scope": null}] | {}     | Mary     |
    And I should get the following proposals:
      | product       | username | result                                                                                                                                                                                            |
      | akeneo_tshirt | Mary     | {"values": {"length": [{"locale": null, "scope": null, "data": {"amount":"12.0000", "unit":"CENTIMETER"}}]}, "review_statuses": {"length": [{"locale": null, "scope": null, "status": "draft"}]}} |
    And the product "akeneo_tshirt" should have the following values:
      | length | 15.0000 CENTIMETER |

  Scenario: Do not create a draft with same values as product
    Given the following product values:
      | product       | attribute | value              |
      | akeneo_tshirt | length    | 20.0000 CENTIMETER |
    Then I should get the following product drafts after apply the following updater to it:
      | product       | actions                                                                                                                   | result | username |
      | akeneo_tshirt | [{"type": "set_data", "field": "length", "data": {"amount":20.0000, "unit":"CENTIMETER"}, "locale": null, "scope": null}] | {}     | Mary     |
    And I should not get the following proposal:
      | product       | username |
      | akeneo_tshirt | Mary     |
