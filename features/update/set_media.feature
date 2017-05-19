Feature: Update media fields
  In order to update products
  As an internal process or any user
  I need to be able to update a media field of a product

  Scenario: Successfully update a media field
    Given a "default" catalog configuration
    And the following attributes:
      | code       | type              | localizable | scopable | allowed_extensions | group |
      | front_view | pim_catalog_image | 1           | 0        | jpg, png           | other |
      | side_view  | pim_catalog_image | 0           | 1        | jpg, png           | other |
      | user_guide | pim_catalog_image | 1           | 1        | png                | other |
    And the following products:
      | sku     |
      | AKN_MUG |
    Then I should get the following products after apply the following updater to it:
      | product | actions                                                                                                            | result                                                                                                      |
      | AKN_MUG | [{"type": "set_data", "field": "front_view", "data": "%fixtures%/SNKRS-1R.png", "locale": "fr_FR", "scope": null}] | {"values": {"front_view": [{"locale": "fr_FR", "scope": null, "data": "6/4/a/0/64a0e9d7c1a28112bbcbcbc06aa52a7032417744_SNKRS_1R.png"}]}} |
      | AKN_MUG | [{"type": "set_data", "field": "side_view", "data": "%fixtures%/SNKRS-1C-t.png", "locale": null, "scope": "mobile"}]                                                                                                                                                                               | {"values": {"side_view": [{"locale": null, "scope": "mobile", "data": {"originalFilename": "side.png"}}]}}           |
      | AKN_MUG | [{"type": "set_data", "field": "user_guide", "data": "%fixtures%/akeneo.jpg", "locale": "fr_FR", "scope": "mobile"}]                                                                                                                                                                         | {"values": {"user_guide": [{"locale": "fr_FR", "scope": "mobile", "data": {"originalFilename": "user_guide.pdf"}}]}} |
      | AKN_MUG | [{"type": "set_data", "field": "user_guide", "data": "%fixtures%/akeneo.jpg", "locale": "fr_FR", "scope": "mobile"}, {"type": "set_data", "field": "user_guide", "data": {"originalFilename": "side_view.pdf", "filePath": "%fixtures%/akeneo2.jpg"}, "locale": "fr_FR", "scope": "mobile"}] | {"values": {"user_guide": [{"locale": "fr_FR", "scope": "mobile", "data": {"originalFilename": "side_view.pdf"}}]}}  |
