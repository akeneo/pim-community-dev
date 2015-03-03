Feature: Update media fields
  In order to update products
  As an internal process or any user
  I need to be able to update a media field of a product

  Scenario: Successfully update a media field
    Given a "default" catalog configuration
    And the following attributes:
      | code       | type | localizable | scopable | allowedExtensions |
      | front_view | file | yes         | no       | jpg, png          |
      | side_view  | file | no          | yes      | jpg, png          |
      | user_guide | file | yes         | yes      | png               |
    And the following products:
      | sku     |
      | AKN_MUG |
    Then I should get the following products after apply the following updater to it:
      | product | actions                                                                                                                                                                                                                                                                                                                                                                                                 | result                                                                                                                |
      | AKN_MUG | [{"type": "set_value", "field": "front_view", "value": {"originalFilename": "front.png", "filePath": "../../../features/Context/fixtures/SNKRS-1R.png"}, "locale": "fr_FR", "scope": null}]                                                                                                                                                                                                             | {"values": {"front_view": [{"locale": "fr_FR", "scope": null, "value": {"originalFilename": "front.png"}}]}}          |
      | AKN_MUG | [{"type": "set_value", "field": "side_view", "value": {"originalFilename": "side.png", "filePath": "../../../features/Context/fixtures/SNKRS-1C-t.png"}, "locale": null, "scope": "mobile"}]                                                                                                                                                                                                            | {"values": {"side_view": [{"locale": null, "scope": "mobile", "value": {"originalFilename": "side.png"}}]}}           |
      | AKN_MUG | [{"type": "set_value", "field": "user_guide", "value": {"originalFilename": "user_guide.pdf", "filePath": "../../../features/Context/fixtures/SNKRS-1R.png"}, "locale": "fr_FR", "scope": "mobile"}]                                                                                                                                                                                                    | {"values": {"user_guide": [{"locale": "fr_FR", "scope": "mobile", "value": {"originalFilename": "user_guide.pdf"}}]}} |
      | AKN_MUG | [{"type": "set_value", "field": "user_guide", "value": {"originalFilename": "user_guide.pdf", "filePath": "../../../features/Context/fixtures/SNKRS-1R.png"}, "locale": "fr_FR", "scope": "mobile"}, {"type": "set_value", "field": "user_guide", "value": {"originalFilename": "side_view.pdf", "filePath": "../../../features/Context/fixtures/SNKRS-1R.png"}, "locale": "fr_FR", "scope": "mobile"}] | {"values": {"user_guide": [{"locale": "fr_FR", "scope": "mobile", "value": {"originalFilename": "side_view.pdf"}}]}}  |
