Feature: Create a draft with a simple select fields
  In order to update products
  As a redactor user
  I need to be able to create a draft with a simple select field

  Background:
    Given a "clothing" catalog configuration
    And the following product:
      | sku           | categories |
      | akeneo_tshirt | tops       |

  Scenario: Successfully add a draft without add attribute in product
    Given I should get the following product drafts after apply the following updater to it:
      | product       | actions                                                                               | result | username |
      | akeneo_tshirt | [{"type": "set_data", "field": "rating", "data": "2", "locale": null, "scope": null}] | {}     | Mary     |
    And I should get the following proposals:
      | product       | username | result                                                                                                                                                      |
      | akeneo_tshirt | Mary     | {"values": {"rating": [{"locale": null, "scope": null, "data": "2"}]}, "review_statuses": {"rating": [{"locale": null, "scope": null, "status": "draft"}]}} |
    And the product "akeneo_tshirt" should have the following values:
      | rating |  |

  Scenario: Successfully add a draft without update attribute in product
    Given the following product values:
      | product       | attribute | value |
      | akeneo_tshirt | rating    | 3     |
    Then I should get the following product drafts after apply the following updater to it:
      | product       | actions                                                                               | result | username |
      | akeneo_tshirt | [{"type": "set_data", "field": "rating", "data": "2", "locale": null, "scope": null}] | {}     | Mary     |
    And I should get the following proposals:
      | product       | username | result                                                                                                                                                      |
      | akeneo_tshirt | Mary     | {"values": {"rating": [{"locale": null, "scope": null, "data": "2"}]}, "review_statuses": {"rating": [{"locale": null, "scope": null, "status": "draft"}]}} |
    And the product "akeneo_tshirt" should have the following values:
      | rating | [3] |

  Scenario: Do not create a draft with same values as product
    Given the following product values:
      | product       | attribute | value |
      | akeneo_tshirt | rating    | 3     |
    Then I should get the following product drafts after apply the following updater to it:
      | product       | actions                                                                               | result | username |
      | akeneo_tshirt | [{"type": "set_data", "field": "rating", "data": "3", "locale": null, "scope": null}] | {}     | Mary     |
    And I should not get the following proposal:
      | product       | username |
      | akeneo_tshirt | Mary     |
