Feature: Update association fields
  In order to update products
  As an internal process or any user
  I need to be able to update the association field of a product

  Scenario: Successfully update the association field
    Given a "apparel" catalog configuration
    And the following products:
      | sku             |
      | owner           |
      | associatedOne   |
      | associatedTwo   |
      | associatedThree |
    And the following product groups:
      | code       | label-en_US | type   |
      | groupOne   | One         | upsell |
      | groupTwo   | Two         | upsell |
      | groupThree | Three       | upsell |
    Then I should get the following products after apply the following updater to it:
      | product | actions                                                                                                                                                    | result                                                                                                                                      |
      | owner   | [{"type": "add_data", "field": "associations", "data": {"similar":{"products":["associatedOne"], "groups":[]}}}]                                           | {"associations":{"similar":{"products":["associatedOne"]}}}                                                                                 |
      | owner   | [{"type": "add_data", "field": "associations", "data": {"similar":{"products":[], "groups":["groupOne"]}}}]                                                | {"associations":{"similar":{"products":["associatedOne"],"groups":["groupOne"]}}}                                                           |
      | owner   | [{"type": "add_data", "field": "associations", "data": {"similar":{"products":["associatedTwo", "associatedThree"], "groups":["groupTwo","groupThree"]}}}] | {"associations":{"similar":{"products":["associatedOne","associatedTwo","associatedThree"],"groups":["groupOne","groupTwo","groupThree"]}}} |
