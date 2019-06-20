Feature: Create a reference entity
  In order to create a reference entity
  As a user
  I want create a reference entity

  @acceptance-back
  Scenario: Creating a reference entity
    When the user creates a reference entity "designer" with:
      | labels                                    |
      | {"en_US": "Stylist", "fr_FR": "Styliste"} |
    Then there is a reference entity "designer" with:
      | identifier | labels                                    |
      | designer   | {"en_US": "Stylist", "fr_FR": "Styliste"} |

  @acceptance-back
  Scenario: Creating a reference entity with no labels
    When the user creates a reference entity "designer" with:
      | labels |
      | {}     |
    Then there is a reference entity "designer" with:
      | identifier | labels |
      | [designer] | {}     |

  @acceptance-back
  Scenario: Cannot create a reference entity with invalid identifier
    When the user creates a reference entity "invalid/identifier" with:
      | labels |
      | {}     |
    Then an exception is thrown with message "Reference entity identifier may contain only letters, numbers and underscores. "invalid/identifier" given"
    And there should be no reference entity

  @acceptance-back
  Scenario: Cannot create more reference entities than the limit
    Given 100 random reference entities
    When the user creates a reference entity "color" with:
      | labels                                 | image |
      | {"en_US": "Color", "fr_FR": "Couleur"} | null  |
    Then there should be a validation error with message 'You cannot create the reference entity "Color" because you have reached the limit of 100 reference entities'

  @acceptance-back
  Scenario: An attribute as label is created alongside the reference entity
    When the user creates a reference entity "designer" with:
      | labels                                     |
      | {"en_US": "Designer", "fr_FR": "Designer"} |
    Then there is a text attribute "label" in the reference entity "designer" with:
      | code  | labels | is_required | order | value_per_channel | value_per_locale | max_length | type | is_textarea | is_rich_text_editor | validation_rule | regular_expression |
      | label | {}     | false       | 0     | false             | true             | null       | text | false       | false               | none            |                    |
    And the reference entity "designer" should be:
      | identifier | labels                                     | attribute_as_label |
      | designer   | {"en_US": "Designer", "fr_FR": "Designer"} | label              |

  @acceptance-back
  Scenario: An attribute as image is created alongside the reference entity
    When the user creates a reference entity "designer" with:
      | labels                                     |
      | {"en_US": "Designer", "fr_FR": "Designer"} |
    Then there is an image attribute "image" in the reference entity "designer" with:
      | code  | labels | is_required | order | value_per_channel | value_per_locale | max_file_size | allowed_extensions | type  |
      | image | {}     | false       | 1     | false             | false            | null          | []                 | image |
    And the reference entity "designer" should be:
      | identifier | labels                                     | attribute_as_image |
      | designer   | {"en_US": "Designer", "fr_FR": "Designer"} | image              |

  @acceptance-front
  Scenario: Creating a reference entity
    Given the user has the following rights:
      | akeneo_referenceentity_reference_entity_create | true |
    When the user creates a reference entity "designer" with:
      | labels                                    |
      | {"en_US": "Stylist", "fr_FR": "Styliste"} |
    Then the reference entity will be saved
    And the user saves the reference entity

  @acceptance-front
  Scenario: User do not have the right to create reference entities
    Given the user has the following rights:
      | akeneo_referenceentity_reference_entity_create | false |
    When the user asks for the reference entity list
    Then the user should not be able to create a reference entity

  @acceptance-front
  Scenario: Cannot create a reference entity with invalid identifier
    Given the user has the following rights:
      | akeneo_referenceentity_reference_entity_create | true |
    When the user creates a reference entity "invalid/identifier" with:
      | labels |
      | {}     |
    Then The validation error will be "This field may only contain letters, numbers and underscores."
    And the user saves the reference entity
    And a validation message is displayed "This field may only contain letters, numbers and underscores."
