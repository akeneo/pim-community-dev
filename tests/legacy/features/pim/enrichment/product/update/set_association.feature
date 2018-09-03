Feature: Update association fields
  In order to update products
  As an internal process or any user
  I need to be able to update the association field of a product

  Scenario: Successfully update the association field
    Given a "apparel" catalog configuration
    And the following products:
      | sku           |
      | ownerOne      |
      | ownerTwo      |
      | ownerThree    |
      | ownerFour     |
      | associatedOne |
      | associatedTwo |
    And the following product groups:
      | code     | label-en_US | type   |
      | groupOne | One         | upsell |
      | groupTwo | Two         | upsell |
    Then I should get the following products after apply the following updater to it:
      | product    | actions                                                                                                                                                                                       | result                                                                                                                                              |
      | ownerOne   | [{"type": "set_data", "field": "associations", "data": {"similar":{"products":["associatedOne"], "groups":[]}}}]                                                                              | {"associations":{"similar":{"products":["associatedOne"]}}}                                                                                         |
      | ownerTwo   | [{"type": "set_data", "field": "associations", "data": {"similar":{"products":["associatedOne"], "groups":["groupOne"]}}}]                                                                    | {"associations":{"similar":{"products":["associatedOne"],"groups":["groupOne"]}}}                                                                   |
      | ownerThree | [{"type": "set_data", "field": "associations", "data": {"similar":{"products":["associatedOne","associatedTwo"], "groups":["groupOne","groupTwo"]}}}]                                         | {"associations":{"similar":{"products":["associatedOne","associatedTwo"],"groups":["groupOne","groupTwo"]}}}                                        |
      | ownerFour  | [{"type": "set_data", "field": "associations", "data": {"similar":{"products":["associatedOne"], "groups":["groupOne"]},"cross_sell":{"products":["associatedTwo"], "groups":["groupTwo"]}}}] | {"associations":{"similar":{"products":["associatedOne"],"groups":["groupOne"]},"cross_sell":{"products":["associatedTwo"],"groups":["groupTwo"]}}} |
