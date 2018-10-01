Feature: Create an reference entity
  In order to create an reference entity
  As a user
  I want create an reference entity

  @acceptance-back
  Scenario: Creating an reference entity
    When the user creates an reference entity "designer" with:
      | labels                                    |
      | {"en_US": "Stylist", "fr_FR": "Styliste"} |
    Then there is an reference entity "designer" with:
      | identifier | labels                                    |
      | designer   | {"en_US": "Stylist", "fr_FR": "Styliste"} |

  @acceptance-back
  Scenario: Creating an reference entity with no labels
    When the user creates an reference entity "designer" with:
      | labels |
      | {}     |
    Then there is an reference entity "designer" with:
      | identifier | labels |
      | [designer] | {}     |

  @acceptance-back
  Scenario: Cannot create an reference entity with invalid identifier
    When the user creates an reference entity "invalid/identifier" with:
      | labels |
      | {}     |
    Then an exception is thrown with message "Reference entity identifier may contain only letters, numbers and underscores. "invalid/identifier" given"
    And there should be no reference entity

  @acceptance-front
  Scenario: Creating an reference entity
    Given the user has the following rights:
      | akeneo_referenceentity_reference_entity_create | true |
    When the user creates an reference entity "designer" with:
      | labels                                    |
      | {"en_US": "Stylist", "fr_FR": "Styliste"} |
    Then the reference entity will be saved
    And the user saves the reference entity

  @acceptance-front
  Scenario: User do not have the right to create reference entities
    Given the user has the following rights:
      | akeneo_referenceentity_reference_entity_create | false |
    When the user asks for the reference entity list
    Then the user should not be able to create an reference entity

  @acceptance-front
  Scenario: Cannot create an reference entity with invalid identifier
    Given the user has the following rights:
      | akeneo_referenceentity_reference_entity_create | true |
    When the user creates an reference entity "invalid/identifier" with:
      | labels |
      | {}     |
    Then The validation error will be "This field may only contain letters, numbers and underscores."
    And the user saves the reference entity
    And a validation message is displayed "This field may only contain letters, numbers and underscores."
