Feature: Update boolean fields
  In order to update products
  As an internal process or any user
  I need to be able to update a boolean field of a product

  Scenario: Successfully update a boolean field
    Given a "default" catalog configuration
    And the following attributes:
      | code    | type    | localizable | scopable |
      | active  | boolean | yes         | no       |
      | awesome | boolean | no          | yes      |
      | pure    | boolean | yes         | yes      |
      | organic | boolean | no          | no       |
    And the following products:
      | sku      |
      | active   |
      | awesome  |
      | pure     |
      | orgnanic |
      | orgnanic_and_pure |
    Then I should get the following products after apply the following updater to it:
      | product           | actions                                                                                                                                                                            | result                                                                                                                                     |
      | active            | [{"type": "set_data", "field": "active", "data": true, "locale": "fr_FR", "scope": null}]                                                                                          | {"values": {"active": [{"locale": "fr_FR", "scope": null, "value": true}]}}                                                                |
      | awesome           | [{"type": "set_data", "field": "awesome", "data": false, "locale": null, "scope": "mobile"}]                                                                                       | {"values": {"awesome": [{"locale": null, "scope": "mobile", "value": false}]}}                                                             |
      | pure              | [{"type": "set_data", "field": "pure", "data": true, "locale": "fr_FR", "scope": "mobile"}]                                                                                        | {"values": {"pure": [{"locale": "fr_FR", "scope": "mobile", "value": true}]}}                                                              |
      | orgnanic          | [{"type": "set_data", "field": "organic", "data": false, "locale": null, "scope": null}]                                                                                           | {"values": {"organic": [{"locale": null, "scope": null, "value": false}]}}                                                                 |
      | orgnanic_and_pure | [{"type": "set_data", "field": "organic", "data": true, "locale": null, "scope": null}, {"type": "set_data", "field": "pure", "data": true, "locale": "fr_FR", "scope": "mobile"}] | {"values": {"organic": [{"locale": null, "scope": null, "value": true}], "pure": [{"locale": "fr_FR", "scope": "mobile", "value": true}]}} |
