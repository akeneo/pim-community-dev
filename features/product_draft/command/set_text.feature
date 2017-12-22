Feature: Create a draft with a text fields
  In order to update products
  As a redactor user
  I need to be able to create a draft with a text field

  Background:
    Given a "clothing" catalog configuration
    And the following product:
      | sku           | categories |
      | akeneo_tshirt | tops       |

  Scenario: Successfully add a draft without add attribute in product
    Given I should get the following product drafts after apply the following updater to it:
      | product       | actions                                                                                                       | result | username |
      | akeneo_tshirt | [{"type": "set_data", "field": "description", "data": "Tshirt Akeneo", "locale": "en_US", "scope": "mobile"}] | {}     | Mary     |
    And I should get the following proposals:
      | product       | username | result                                                                                                                                                                                          |
      | akeneo_tshirt | Mary     | {"values": {"description": [{"locale": "en_US", "scope": "mobile", "data": "Tshirt Akeneo"}]}, "review_statuses": {"description": [{"locale": "en_US", "scope": "mobile", "status": "draft"}]}} |
    And the product "akeneo_tshirt" should have the following values:
      | description-en_US-mobile |  |

  Scenario: Successfully add a draft without update attribute in product
    Given the following product values:
      | product       | attribute   | value         | locale | scope  |
      | akeneo_tshirt | description | Tshirt Akeneo | en_US  | mobile |
    Then I should get the following product drafts after apply the following updater to it:
      | product       | actions                                                                                                                 | result | username |
      | akeneo_tshirt | [{"type": "set_data", "field": "description", "data": "Wonderful Akeneo Tshirt", "locale": "en_US", "scope": "mobile"}] | {}     | Mary     |
    And I should get the following proposals:
      | product       | username | result                                                                                                                                                                                                    |
      | akeneo_tshirt | Mary     | {"values": {"description": [{"locale": "en_US", "scope": "mobile", "data": "Wonderful Akeneo Tshirt"}]}, "review_statuses": {"description": [{"locale": "en_US", "scope": "mobile", "status": "draft"}]}} |
    And the product "akeneo_tshirt" should have the following values:
      | description-en_US-mobile | Tshirt Akeneo |

  Scenario: Do not create a draft with same values as product
    Given the following product values:
      | product       | attribute   | value         | locale | scope  |
      | akeneo_tshirt | description | Tshirt Akeneo | en_US  | mobile |
    Then I should get the following product drafts after apply the following updater to it:
      | product       | actions                                                                                                       | result | username |
      | akeneo_tshirt | [{"type": "set_data", "field": "description", "data": "Tshirt Akeneo", "locale": "en_US", "scope": "mobile"}] | {}     | Mary     |
    And I should not get the following proposal:
      | product       | username |
      | akeneo_tshirt | Mary     |
