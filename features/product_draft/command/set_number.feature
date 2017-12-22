Feature: Create a draft with a number fields
  In order to update products
  As a redactor user
  I need to be able to create a draft with a number field

  Background:
    Given a "clothing" catalog configuration
    And the following product:
      | sku           | categories |
      | akeneo_tshirt | tops       |

  Scenario: Successfully add a draft without add attribute in product
    Given I should get the following product drafts after apply the following updater to it:
      | product       | actions                                                                                                | result | username |
      | akeneo_tshirt | [{"type": "set_data", "field": "number_in_stock", "data": 12.0000, "locale": null, "scope": "mobile"}] | {}     | Mary     |
    And I should get the following proposals:
      | product       | username | result                                                                                                                                                                                      |
      | akeneo_tshirt | Mary     | {"values": {"number_in_stock": [{"locale": null, "scope": "mobile", "data": "12.0000"}]}, "review_statuses": {"number_in_stock": [{"locale": null, "scope": "mobile", "status": "draft"}]}} |
    And the product "akeneo_tshirt" should have the following values:
      | number_in_stock-mobile |  |

  Scenario: Successfully add a draft without update attribute in product
    Given the following product values:
      | product       | attribute       | value   | scope  |
      | akeneo_tshirt | number_in_stock | 15.0000 | mobile |
    Then I should get the following product drafts after apply the following updater to it:
      | product       | actions                                                                                                | result | username |
      | akeneo_tshirt | [{"type": "set_data", "field": "number_in_stock", "data": 12.0000, "locale": null, "scope": "mobile"}] | {}     | Mary     |
    And I should get the following proposals:
      | product       | username | result                                                                                                                                                                                      |
      | akeneo_tshirt | Mary     | {"values": {"number_in_stock": [{"locale": null, "scope": "mobile", "data": "12.0000"}]}, "review_statuses": {"number_in_stock": [{"locale": null, "scope": "mobile", "status": "draft"}]}} |
    And the product "akeneo_tshirt" should have the following values:
      | number_in_stock-mobile | 15.0000 |

  Scenario: Do not create a draft with same values as product
    Given the following product values:
      | product       | attribute       | value   | scope  |
      | akeneo_tshirt | number_in_stock | 20.0000 | mobile |
    Then I should get the following product drafts after apply the following updater to it:
      | product       | actions                                                                                                | result | username |
      | akeneo_tshirt | [{"type": "set_data", "field": "number_in_stock", "data": 20.0000, "locale": null, "scope": "mobile"}] | {}     | Mary     |
    And I should not get the following proposal:
      | product       | username |
      | akeneo_tshirt | Mary     |
