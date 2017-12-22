Feature: Create a draft with a simple reference data fields
  In order to update products
  As a redactor user
  I need to be able to create a draft with a simple reference data field

  Background:
    Given a "clothing" catalog configuration
    And the following attributes:
      | code      | label-en_US | type                            | reference_data_name | group |
      | top_color | Main color  | pim_reference_data_simpleselect | color               | other |
    And the following "top_color" attribute reference data: Red, Green, Light green, Blue, Yellow, Cyan
    And the following product:
      | sku           | categories |
      | akeneo_tshirt | tops       |

  Scenario: Successfully add a draft without add attribute in product
    Given I should get the following product drafts after apply the following updater to it:
      | product       | actions                                                                                     | result | username |
      | akeneo_tshirt | [{"type": "set_data", "field": "lace_color", "data": "Red", "locale": null, "scope": null}] | {}     | Mary     |
    And I should get the following proposals:
      | product       | username | result                                                                                                                                                                |
      | akeneo_tshirt | Mary     | {"values": {"lace_color": [{"locale": null, "scope": null, "data": "Red"}]}, "review_statuses": {"lace_color": [{"locale": null, "scope": null, "status": "draft"}]}} |
    And the product "akeneo_tshirt" should have the following values:
      | lace_color |  |

  Scenario: Successfully add a draft without update attribute in product
    Given the following product values:
      | product       | attribute  | value |
      | akeneo_tshirt | lace_color | Green |
    Then I should get the following product drafts after apply the following updater to it:
      | product       | actions                                                                                      | result | username |
      | akeneo_tshirt | [{"type": "set_data", "field": "lace_color", "data": "Blue", "locale": null, "scope": null}] | {}     | Mary     |
    And I should get the following proposals:
      | product       | username | result                                                                                                                                                                 |
      | akeneo_tshirt | Mary     | {"values": {"lace_color": [{"locale": null, "scope": null, "data": "Blue"}]}, "review_statuses": {"lace_color": [{"locale": null, "scope": null, "status": "draft"}]}} |
    And the product "akeneo_tshirt" should have the following values:
      | lace_color | Green |

  Scenario: Do not create a draft with same values as product
    Given the following product values:
      | product       | attribute  | value |
      | akeneo_tshirt | lace_color | Blue  |
    Then I should get the following product drafts after apply the following updater to it:
      | product       | actions                                                                                      | result | username |
      | akeneo_tshirt | [{"type": "set_data", "field": "lace_color", "data": "Blue", "locale": null, "scope": null}] | {}     | Mary     |
    And I should not get the following proposal:
      | product       | username |
      | akeneo_tshirt | Mary     |
