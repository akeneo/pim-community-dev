Feature: Create an asset family
  In order to create an asset family
  As a user
  I want create an asset family

  @acceptance-back
  Scenario: Creating an asset family
    When the user creates an asset family "designer" with:
      | labels                                    |
      | {"en_US": "Stylist", "fr_FR": "Styliste"} |
    Then there is an asset family "designer" with:
      | identifier | labels                                    |
      | designer   | {"en_US": "Stylist", "fr_FR": "Styliste"} |

  @acceptance-back
  Scenario: Creating an asset family with no labels
    When the user creates an asset family "designer" with:
      | labels |
      | {}     |
    Then there is an asset family "designer" with:
      | identifier | labels |
      | [designer] | {}     |

  @acceptance-back
  Scenario: Cannot create an asset family with invalid identifier
    When the user creates an asset family "invalid/identifier" with:
      | labels |
      | {}     |
    Then an exception is thrown with message "Asset family identifier may contain only letters, numbers and underscores. "invalid/identifier" given"
    And there should be no asset family

  @acceptance-back
  Scenario: Cannot create more asset families than the limit
    Given 100 random asset families
    When the user creates an asset family "color" with:
      | labels                                 | image |
      | {"en_US": "Color", "fr_FR": "Couleur"} | null  |
    Then there should be a validation error with message 'You cannot create the asset family "Color" because you have reached the limit of 100 asset families'

  @acceptance-back
  Scenario: An attribute as label is created alongside the asset family
    When the user creates an asset family "designer" with:
      | labels                                     |
      | {"en_US": "Designer", "fr_FR": "Designer"} |
    Then there is a text attribute "label" in the asset family "designer" with:
      | code  | labels | is_required | order | value_per_channel | value_per_locale | max_length | type | is_textarea | is_rich_text_editor | validation_rule | regular_expression |
      | label | {}     | false       | 0     | false             | true             | null       | text | false       | false               | none            |                    |
    And the asset family "designer" should be:
      | identifier | labels                                     | attribute_as_label |
      | designer   | {"en_US": "Designer", "fr_FR": "Designer"} | label              |

  @acceptance-back
  Scenario: An attribute as image is created alongside the asset family
    When the user creates an asset family "designer" with:
      | labels                                     |
      | {"en_US": "Designer", "fr_FR": "Designer"} |
    Then there is an image attribute "image" in the asset family "designer" with:
      | code  | labels | is_required | order | value_per_channel | value_per_locale | max_file_size | allowed_extensions | type  |
      | image | {}     | false       | 1     | false             | false            | null          | []                 | image |
    And the asset family "designer" should be:
      | identifier | labels                                     | attribute_as_image |
      | designer   | {"en_US": "Designer", "fr_FR": "Designer"} | image              |

  @acceptance-back
  Scenario: Creating an asset family with a collection of rule templates
    When the user creates an asset family 'packshot' with a collection of rule templates
    Then there is an asset family 'packshot' with a collection of rule templates

  @acceptance-front
  Scenario: Creating an asset family
    Given the user has the following rights:
      | akeneo_assetmanager_asset_family_create | true |
    When the user creates an asset family "designer" with:
      | labels                                    |
      | {"en_US": "Stylist", "fr_FR": "Styliste"} |
    Then the asset family will be saved
    And the user saves the asset family

  @acceptance-front
  Scenario: User do not have the right to create asset families
    Given the user has the following rights:
      | akeneo_assetmanager_asset_family_create | false |
    When the user asks for the asset family list
    Then the user should not be able to create an asset family

  @acceptance-front
  Scenario: Cannot create an asset family with invalid identifier
    Given the user has the following rights:
      | akeneo_assetmanager_asset_family_create | true |
    When the user creates an asset family "invalid/identifier" with:
      | labels |
      | {}     |
    Then The validation error will be "This field may only contain letters, numbers and underscores."
    And the user saves the asset family
    And a validation message is displayed "This field may only contain letters, numbers and underscores."
