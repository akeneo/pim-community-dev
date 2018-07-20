Feature: Create an enriched entity
  In order to create an enriched entity
  As a user
  I want create an enriched entity

  @acceptance-back
  Scenario: Creating an enriched entity
    When the user creates an enriched entity "designer" with:
      | labels                                    |
      | {"en_US": "Stylist", "fr_FR": "Styliste"} |
    Then there is an enriched entity "designer" with:
      | identifier | labels                                    |
      | designer   | {"en_US": "Stylist", "fr_FR": "Styliste"} |

  @acceptance-back
  Scenario: Creating an enriched entity with no labels
    When the user creates an enriched entity "designer" with:
      | labels |
      | {}     |
    Then there is an enriched entity "designer" with:
      | identifier | labels |
      | [designer] | {}     |

  @acceptance-back
  Scenario: Cannot create an enriched entity with invalid identifier
    When the user creates an enriched entity "invalid/identifier" with:
      | labels |
      | {}     |
    Then an exception is thrown with message "Enriched Entity identifier may contain only letters, numbers and underscores"
    And there should be no enriched entity

  @acceptance-front
  Scenario: Creating an enriched entity
    Given the user has the following rights:
      | akeneo_enrichedentity_enriched_entity_create | true |
    When the user creates an enriched entity "designer" with:
      | labels                                    |
      | {"en_US": "Stylist", "fr_FR": "Styliste"} |
    Then the enriched entity will be saved
    And the user saves the enriched entity
    And there is an enriched entity "designer" with:
      | identifier | labels                                    |
      | designer   | {"en_US": "Stylist", "fr_FR": "Styliste"} |

  @acceptance-front
  Scenario: User do not have the right to create enriched entities
    Given the user has the following rights:
      | akeneo_enrichedentity_enriched_entity_create | false |
    When the user asks for the enriched entity list
    Then the user should not be able to create an enriched entity

  @acceptance-front
  Scenario: Cannot create an enriched entity with invalid identifier
    Given the user has the following rights:
      | akeneo_enrichedentity_enriched_entity_create | true |
    When the user creates an enriched entity "invalid/identifier" with:
      | labels |
      | {}     |
    Then The validation error will be "This field may only contain letters, numbers and underscores."
    And the user saves the enriched entity
    And a validation message is displayed "This field may only contain letters, numbers and underscores."
