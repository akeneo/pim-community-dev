Feature: Create a record
  In order to have records
  As a user
  I want create a record

  Background:
    Given the following enriched entity:
      | identifier | labels                                       | image |
      | designer   | {"en_US": "Designer", "fr_FR": "Concepteur"} | null  |

  @acceptance-back
  Scenario: Creating a record
    When the user creates a record "stark" for entity "designer" with:
      | labels                                            |
      | {"en_US": "Tony Stark", "fr_FR": "Thierry Stark"} |
    Then there is a record with:
      | code  | entity_identifier | labels                                            |
      | stark | designer          | {"en_US": "Tony Stark", "fr_FR": "Thierry Stark"} |

  @acceptance-back
  Scenario: Creating a record with no labels
    When the user creates a record "stark" for entity "designer" with:
      | labels |
      | {}     |
    Then there is a record with:
      | code  | entity_identifier | labels |
      | stark | designer          | {}     |

  @acceptance-back
  Scenario: Cannot create a record with invalid identifier
    When the user creates a record "invalid/identifier" for entity "designer" with:
      | labels |
      | {}     |
    Then an exception is thrown with message "Record code may contain only letters, numbers and underscores. "invalid/identifier" given"
    And there should be no record

  @acceptance-front
  Scenario: Creating a record
    When the user asks for the enriched entity "designer"
    Given the user has the following rights:
      | akeneo_enrichedentity_record_create | true |
    And the user creates a record of "designer" with:
      | code  | labels             |
      | stark | {"en_US": "Stark"} |
    Then the record will be saved
    And the user saves the record
    And there is a record of "designer" with:
      | identifier       | code  | labels             |
      | designer_stark_1 | stark | {"en_US": "Stark"} |

  @acceptance-front
  Scenario: Cannot create a record with invalid identifier
    When the user asks for the enriched entity "designer"
    Given the user has the following rights:
      | akeneo_enrichedentity_record_create | true |
    And the user creates a record of "designer" with:
      | code               | labels |
      | invalid/identifier | {}     |
    Then the record validation error will be "This field may only contain letters, numbers and underscores."
    And the user saves the record
    And a validation message is displayed "This field may only contain letters, numbers and underscores."
