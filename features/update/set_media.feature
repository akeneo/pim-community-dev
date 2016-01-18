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
      | product | actions                                                                                                                                                                                                                                                                                                                                                                                             | result                                                                                                               |
      | AKN_MUG | [{"type": "set_data", "field": "front_view", "data": {"originalFilename": "front.png", "filePath": "%fixtures%/SNKRS-1R.png"}, "locale": "fr_FR", "scope": null}]                                                                                                                                                                                                           | {"values": {"front_view": [{"locale": "fr_FR", "scope": null, "data": {"originalFilename": "front.png"}}]}}          |
      | AKN_MUG | [{"type": "set_data", "field": "side_view", "data": {"originalFilename": "side.png", "filePath": "%fixtures%/SNKRS-1C-t.png"}, "locale": null, "scope": "mobile"}]                                                                                                                                                                                                          | {"values": {"side_view": [{"locale": null, "scope": "mobile", "data": {"originalFilename": "side.png"}}]}}           |
      | AKN_MUG | [{"type": "set_data", "field": "user_guide", "data": {"originalFilename": "user_guide.pdf", "filePath": "%fixtures%/SNKRS-1R.png"}, "locale": "fr_FR", "scope": "mobile"}]                                                                                                                                                                                                  | {"values": {"user_guide": [{"locale": "fr_FR", "scope": "mobile", "data": {"originalFilename": "user_guide.pdf"}}]}} |
      | AKN_MUG | [{"type": "set_data", "field": "user_guide", "data": {"originalFilename": "user_guide.pdf", "filePath": "%fixtures%/SNKRS-1R.png"}, "locale": "fr_FR", "scope": "mobile"}, {"type": "set_data", "field": "user_guide", "data": {"originalFilename": "side_view.pdf", "filePath": "%fixtures%/SNKRS-1R.png"}, "locale": "fr_FR", "scope": "mobile"}] | {"values": {"user_guide": [{"locale": "fr_FR", "scope": "mobile", "data": {"originalFilename": "side_view.pdf"}}]}}  |
