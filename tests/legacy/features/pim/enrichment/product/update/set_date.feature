Feature: Update date fields
  In order to update products
  As an internal process or any user
  I need to be able to update a date field of a product

  Scenario: Successfully update a date field
    Given a "default" catalog configuration
    And the following attributes:
      | code          | type             | localizable | scopable | group |
      | release_date  | pim_catalog_date | 1           | 0        | other |
      | end_date      | pim_catalog_date | 0           | 1        | other |
      | publish_date  | pim_catalog_date | 1           | 1        | other |
      | creation_date | pim_catalog_date | 0           | 0        | other |
    And the following products:
      | sku     |
      | AKN_MUG |
    Then I should get the following products after apply the following updater to it:
      | product | actions                                                                                                                                                                                                    | result                                                                                                      |
      | AKN_MUG | [{"type": "set_data", "field": "release_date", "data": "2014-02-18", "locale": "fr_FR", "scope": null}]                                                                                                    | {"values": {"release_date": [{"locale": "fr_FR", "scope": null, "data": "2014-02-18T00:00:00+00:00"}]}}     |
      | AKN_MUG | [{"type": "set_data", "field": "end_date", "data": "3018-01-18", "locale": null, "scope": "mobile"}]                                                                                                       | {"values": {"end_date": [{"locale": null, "scope": "mobile", "data": "3018-01-18T00:00:00+00:00"}]}}        |
      | AKN_MUG | [{"type": "set_data", "field": "publish_date", "data": "2010-05-18", "locale": "fr_FR", "scope": "mobile"}]                                                                                                | {"values": {"publish_date": [{"locale": "fr_FR", "scope": "mobile", "data": "2010-05-18T00:00:00+00:00"}]}} |
      | AKN_MUG | [{"type": "set_data", "field": "creation_date", "data": "2016-01-10", "locale": null, "scope": null}]                                                                                                      | {"values": {"creation_date": [{"locale": null, "scope": null, "data": "2016-01-10T00:00:00+00:00"}]}}       |
      | AKN_MUG | [{"type": "set_data", "field": "creation_date", "data": "2016-01-10", "locale": null, "scope": null}, {"type": "set_data", "field": "creation_date", "data": "2016-01-15", "locale": null, "scope": null}] | {"values": {"creation_date": [{"locale": null, "scope": null, "data": "2016-01-15T00:00:00+00:00"}]}}       |
