Feature: Update media fields
  In order to update products
  As an internal process or any user
  I need to be able to copy a media field of a product

  Scenario: Successfully update a media field
    Given a "default" catalog configuration
    And the following attributes:
      | code       | type | allowedExtensions |
      | front_view | file | jpg, png          |
      | side_view  | file | jpg, png          |
    And the following products:
      | sku     |
      | AKN_MUG |
    And the following product values:
      | product | attribute  | value                 |
      | AKN_MUG | front_view | %fixtures%/akeneo.jpg |
    Then I should get the following products after apply the following updater to it:
      | product | actions                                                                      | result                                                                    |
      | AKN_MUG | [{"type": "copy_data", "from_field": "front_view", "to_field": "side_view"}] | {"values": {"side_view": [{"data": {"originalFilename": "akeneo.jpg"}}]}} |
