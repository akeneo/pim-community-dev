Feature: Create a draft with a multi select fields
  In order to update products
  As a redactor user
  I need to be able to create a draft with a multi select field

  Background:
    Given a "clothing" catalog configuration
    And the following product:
      | sku           | categories |
      | akeneo_tshirt | tops       |

  Scenario: Successfully add a draft without add attribute in product
    Given I should get the following product drafts after apply the following updater to it:
      | product       | actions                                                                                                      | result | username |
      | akeneo_tshirt | [{"type": "set_data", "field": "weather_conditions", "data": ["Dry", "Wet"], "locale": null, "scope": null}] | {}     | Mary     |
    And I should get the following proposals:
      | product       | username | result                                                                                                                                                                                         |
      | akeneo_tshirt | Mary     | {"values": {"weather_conditions": [{"locale": null, "scope": null, "data": ["dry", "wet"]}]}, "review_statuses": {"weather_conditions": [{"locale": null, "scope": null, "status": "draft"}]}} |
    And the product "akeneo_tshirt" should have the following values:
      | weather_conditions |  |

  Scenario: Successfully add a draft without update attribute in product
    Given the following product values:
      | product       | attribute          | value |
      | akeneo_tshirt | weather_conditions | Dry   |
    Then I should get the following product drafts after apply the following updater to it:
      | product       | actions                                                                                                              | result | username |
      | akeneo_tshirt | [{"type": "set_data", "field": "weather_conditions", "data": ["Cold", "Dry", "Wet"], "locale": null, "scope": null}] | {}     | Mary     |
    And I should get the following proposals:
      | product       | username | result                                                                                                                                                                                                |
      | akeneo_tshirt | Mary     | {"values": {"weather_conditions": [{"locale": null, "scope": null, "data":["cold", "dry", "wet"]}]}, "review_statuses": {"weather_conditions": [{"locale": null, "scope": null, "status": "draft"}]}} |
    And the product "akeneo_tshirt" should have the following values:
      | weather_conditions | [dry] |

  Scenario: Do not create a draft with same values as product
    Given the following product values:
      | product       | attribute          | value   |
      | akeneo_tshirt | weather_conditions | Dry,Wet |
    Then I should get the following product drafts after apply the following updater to it:
      | product       | actions                                                                                                      | result | username |
      | akeneo_tshirt | [{"type": "set_data", "field": "weather_conditions", "data": ["Dry", "Wet"], "locale": null, "scope": null}] | {}     | Mary     |
    And I should not get the following proposal:
      | product       | username |
      | akeneo_tshirt | Mary     |
