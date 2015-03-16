Feature: Update boolean fields
  In order to update products
  As an internal process or any user
  I need to be able to update a copy boolean field of a product

  Scenario: Successfully update a text field
    Given a "apparel" catalog configuration
    And the following attributes:
      | code        | type    |
      | is_discount | boolean |
    And the following products:
      | sku  | handmade |
      | AKN1 | true     |
      | AKN2 | false    |
    Then I should get the following products after apply the following updater to it:
      | product | actions                                                                       | result                                          |
      | AKN1    | [{"type": "copy_value", "from_field": "handmade", "to_field": "is_discount"}] | {"values": {"is_discount": [{"value": true}]}}  |
      | AKN2    | [{"type": "copy_value", "from_field": "handmade", "to_field": "is_discount"}] | {"values": {"is_discount": [{"value": false}]}} |