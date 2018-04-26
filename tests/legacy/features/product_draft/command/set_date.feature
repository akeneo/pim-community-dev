Feature: Create a draft with a date fields
  In order to update products
  As a redactor user
  I need to be able to create a draft with a date field

  Background:
    Given a "clothing" catalog configuration
    And the following product:
      | sku           | categories |
      | akeneo_tshirt | tops       |

  Scenario: Successfully add a draft without add attribute in product
    Given I should get the following product drafts after apply the following updater to it:
      | product       | actions                                                                                                  | result | username |
      | akeneo_tshirt | [{"type": "set_data", "field": "release_date", "data": "2014-02-18", "locale": null, "scope": "mobile"}] | {}     | Mary     |
    And I should get the following proposals:
      | product       | username | result                                                                                                                                                                                                  |
      | akeneo_tshirt | Mary     | {"values": {"release_date": [{"locale": null, "scope": "mobile", "data": "2014-02-18T00:00:00+00:00"}]}, "review_statuses": {"release_date": [{"locale": null, "scope": "mobile", "status": "draft"}]}} |
    And the product "akeneo_tshirt" should have the following values:
      | release_date-mobile |  |

  Scenario: Successfully add a draft without update attribute in product
    Given the following product values:
      | product       | attribute    | value      | scope  |
      | akeneo_tshirt | release_date | 2014-12-18 | mobile |
    Then I should get the following product drafts after apply the following updater to it:
      | product       | actions                                                                                                  | result | username |
      | akeneo_tshirt | [{"type": "set_data", "field": "release_date", "data": "2014-02-18", "locale": null, "scope": "mobile"}] | {}     | Mary     |
    And I should get the following proposals:
      | product       | username | result                                                                                                                                                                                                  |
      | akeneo_tshirt | Mary     | {"values": {"release_date": [{"locale": null, "scope": "mobile", "data": "2014-02-18T00:00:00+00:00"}]}, "review_statuses": {"release_date": [{"locale": null, "scope": "mobile", "status": "draft"}]}} |
    And the product "akeneo_tshirt" should have the following values:
      | release_date-mobile | 2014-12-18 |

  Scenario: Do not create a draft with same values as product
    Given the following product values:
      | product       | attribute    | value      | scope  |
      | akeneo_tshirt | release_date | 2014-12-18 | mobile |
    Then I should get the following product drafts after apply the following updater to it:
      | product       | actions                                                                                                                 | result | username |
      | akeneo_tshirt | [{"type": "set_data", "field": "release_date", "data": "2014-12-18T00:00:00+00:00", "locale": null, "scope": "mobile"}] | {}     | Mary     |
    And I should not get the following proposal:
      | product       | username |
      | akeneo_tshirt | Mary     |
