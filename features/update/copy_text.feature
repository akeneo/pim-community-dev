Feature: Update text fields
  In order to update products
  As an internal process or any user
  I need to be able to update a copy text field of a product

  Scenario: Successfully update a text field
    Given a "apparel" catalog configuration
    And the following products:
      | sku | name-en_US   |
      | AKN | Name to copy |
    Then I should get the following products after apply the following updater to it:
      | product | actions                                                                                                           | result                                                                               |
      | AKN     | [{"type": "copy_data", "from_field": "name", "to_field": "legend", "from_locale": "en_US", "to_locale": "en_US"}] | {"values": {"legend": [{"locale": "en_US", "scope": null, "data": "Name to copy"}]}} |
