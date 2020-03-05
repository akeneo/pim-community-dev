Feature: Create an asset family
  In order to create an asset family
  As a user
  I want create an asset family

  @acceptance-back
  Scenario: Creating an asset family
    When the user creates an asset family "packshot" with:
      | labels                                    | product_link_rules                                                                                                                                                    |
      | {"en_US": "Stylist", "fr_FR": "Styliste"} | [{"product_selections": [{"field": "color", "operator": "=", "value": "red", "channel": "ecommerce", "locale": "fr_FR"}], "assign_assets_to": [{ "mode": "add", "attribute": "my_asset_collection" }]}] |
    Then there is an asset family "packshot" with:
      | identifier | labels                                    | product_link_rules                                                                                                                                                                                                                      |
      | designer   | {"en_US": "Stylist", "fr_FR": "Styliste"} | [{"product_selections": [{"field": "color", "operator": "=", "value": "red", "channel": "ecommerce", "locale": "fr_FR" }], "assign_assets_to": [{ "mode": "add", "attribute": "my_asset_collection", "channel": null, "locale": null }]}] |

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
    Then there should be a validation error with message 'This field may only contain letters, numbers and underscores.'
    And there should be no asset family

  @acceptance-back
  Scenario: Cannot create more asset families than the limit
    Given 100 random asset families
    When the user creates an asset family "color" with:
      | labels                                 | image |
      | {"en_US": "Color", "fr_FR": "Couleur"} | null  |
    Then there should be a validation error with message 'You cannot create the asset family "color" because you have reached the limit of 100 asset families'

  @acceptance-back
  Scenario: An attribute as label is created alongside the asset family
    When the user creates an asset family "designer" with:
      | labels                                     |
      | {"en_US": "Designer", "fr_FR": "Designer"} |
    Then there is a text attribute "label" in the asset family "designer" with:
      | code  | labels | is_required | is_read_only | order | value_per_channel | value_per_locale | max_length | type | is_textarea | is_rich_text_editor | validation_rule | regular_expression |
      | label | {}     | false       | false        | 0     | false             | true             | null       | text | false       | false               | none            |                    |
    And the asset family "designer" should be:
      | identifier | labels                                     | attribute_as_label |
      | designer   | {"en_US": "Designer", "fr_FR": "Designer"} | label              |

  @acceptance-back
  Scenario: An attribute as main media is created alongside the asset family
    When the user creates an asset family "designer" with:
      | labels                                     |
      | {"en_US": "Designer", "fr_FR": "Designer"} |
    Then there is a media file attribute "media" in the asset family "designer" with:
      | code  | labels | is_required | is_read_only | order | value_per_channel | value_per_locale | max_file_size | allowed_extensions | type       | media_type |
      | media | {}     | false       | false        | 1     | false             | false            | null          | []                 | media_file | image      |
    And the asset family "designer" should be:
      | identifier | labels                                     | attribute_as_main_media |
      | designer   | {"en_US": "Designer", "fr_FR": "Designer"} | media                   |

  @acceptance-back
  Scenario: Creating an asset family with a collection of static rule templates
    When the user creates an asset family 'packshot' with a collection of static rule templates
    Then there is no violations errors
    Then there is an asset family 'packshot' with a collection of static rule templates

  @acceptance-back
  Scenario: Cannot create an asset family with a collection of rule templates that contains more than 2 items
    When the user tries to create an asset family 'packshot' with a collection of rule templates having more items than the limit
    Then there should be a validation error with message 'You have reached the limit of 2 product link rules for the "packshot" asset family.'

  @acceptance-back
  Scenario: Cannot create an asset family if there is no product selections
    When the user creates an asset family with an empty product selections
    Then there should be a validation error with message 'You must specify at least one product selection in your product link rule'

  @acceptance-back
  Scenario: Cannot create an asset family if there is no product assignment
    When the user creates an asset family with an empty product assignment
    Then there should be a validation error with message 'You must specify at least one product assignment in your product link rule'

  @acceptance-back
  Scenario: Cannot create an asset family if one of the product link rule is not executable by the rule engine
    When the user creates an asset family with a product link rule not executable by the rule engine
    Then there should be a validation error stating why the rule engine cannot execute the product link rule

  @acceptance-back
  Scenario: Cannot create an asset family if one of the product link rule has an extrapolated product selection field which references an unexisting attribute
    When the user creates an asset family with a product link rule having an extrapolated product selection field which references an attribute that does not exist
    Then there should be a validation error stating that the product link rule cannot be created because the extrapolated product selection field references an attribute that does not exist

  @acceptance-back
  Scenario: Cannot create an asset family if one of the product link rule has an extrapolated product selection value which references an unexisting attribute
    When the user creates an asset family with a product link rule having an extrapolated product selection value which references an attribute that does not exist
    Then there should be a validation error stating that the product link rule cannot be created because the extrapolated product selection value references an attribute that does not exist

  @acceptance-back
  Scenario: Cannot create an asset family if one of the product link rule has an extrapolated product assignment attribute which references an unexisting attribute
    When the user creates an asset family with a product link rule having an extrapolated product assignment attribute which references an attribute that does not exist
    Then there should be a validation error stating that the product link rule cannot be created because the extrapolated product assignment attribute references an attribute that does not exist

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
