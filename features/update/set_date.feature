Feature: Update boolean fields
  In order to update products
  As an internal process or any user
  I need to be able to update a boolean field of a product

  Scenario: Successfully update a boolean field
    Given a "default" catalog configuration
    And the following attributes:
      | code    | type    | localizable | scopable |
      | release_date  | date | yes         | no       |
      | end_date  | date | no         | yes       |
      | publish_date  | date | yes         | yes       |
      | creation_date  | date | no         | no       |
    And the following products:
      | sku     |
      | AKN_MUG |
    Then I should get the following products after apply the following updater to it:
      | product | actions                                                                                                                                                                                                        | result                                                                                        |
      | AKN_MUG | [{"type": "set_value", "field": "release_date", "value": "2014-02-18", "locale": "fr_FR", "scope": null}]                                                                                                      | {"values": {"release_date": [{"locale": "fr_FR", "scope": null, "value": "2014-02-18"}]}}     |
      | AKN_MUG | [{"type": "set_value", "field": "end_date", "value": "3018-01-18", "locale": null, "scope": "mobile"}]                                                                                                         | {"values": {"end_date": [{"locale": null, "scope": "mobile", "value": "3018-01-18"}]}}        |
      | AKN_MUG | [{"type": "set_value", "field": "publish_date", "value": "2010-05-18", "locale": "fr_FR", "scope": "mobile"}]                                                                                                  | {"values": {"publish_date": [{"locale": "fr_FR", "scope": "mobile", "value": "2010-05-18"}]}} |
      | AKN_MUG | [{"type": "set_value", "field": "creation_date", "value": "2016-01-10", "locale": null, "scope": null}]                                                                                                        | {"values": {"creation_date": [{"locale": null, "scope": null, "value": "2016-01-10"}]}}       |
      | AKN_MUG | [{"type": "set_value", "field": "creation_date", "value": "2016-01-10", "locale": null, "scope": null}, {"type": "set_value", "field": "creation_date", "value": "2016-01-15", "locale": null, "scope": null}] | {"values": {"creation_date": [{"locale": null, "scope": null, "value": "2016-01-15"}]}}       |
