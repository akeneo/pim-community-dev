Feature: Update date fields
  In order to update products
  As an internal process or any user
  I need to be able to copy a date field of a product

  Scenario: Successfully update a date field
    Given a "default" catalog configuration
    And the following attributes:
      | code         | type             | group |
      | release_date | pim_catalog_date | other |
      | end_date     | pim_catalog_date | other |
    And the following products:
      | sku     | release_date |
      | AKN_MUG | 2014-02-18   |
    Then I should get the following products after apply the following updater to it:
      | product | actions                                                                       | result                                                            |
      | AKN_MUG | [{"type": "copy_data", "from_field": "release_date", "to_field": "end_date"}] | {"values": {"end_date": [{"data": "2014-02-18T00:00:00+00:00"}]}} |
