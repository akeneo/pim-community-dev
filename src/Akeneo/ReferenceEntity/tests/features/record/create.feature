Feature: Create a record
  In order to have records
  As a user
  I want to create a record

  Background:
    Given a valid reference entity

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

  @acceptance-back
  Scenario: Cannot create more records for a reference entity than the limit
    Given 1000 random records for a reference entity
    When the user creates a record "stark" for entity "designer" with:
      | labels              |
      | {"en_US": "Starck"} |
    Then there should be a validation error with message 'You cannot create the record "Starck" because you have reached the limit of 1000 records for this reference entity'

  @acceptance-front
  Scenario: Creating a record
    When the user asks for the reference entity "designer"
    Given the user has the following rights:
      | akeneo_referenceentity_record_create | true |
    And the user creates a record of "designer" with:
      | code  | labels             |
      | stark | {"en_US": "Stark"} |
    Then the record will be saved
    And the user saves the record

  @acceptance-front
  Scenario: Creating multiple records in sequence
    When the user asks for the reference entity "designer"
    Given the user has the following rights:
      | akeneo_referenceentity_record_create | true |
    And the user creates a record of "designer" with:
      | code  | labels             |
      | stark | {"en_US": "Stark"} |
    Then the record will be saved
    And the user toggles the sequantial creation
    And the user saves the record
    Then the record creation form should be displayed

  @acceptance-front
  Scenario: User doesn't have the right to create a record
    Given the user has the following rights:
      | akeneo_referenceentity_record_create | false |
    When the user asks for the reference entity "designer"
    Then the user cannot create a record

#  @acceptance-front
#  Scenario: Cannot create a record with invalid identifier
#    When the user asks for the reference entity "designer"
#    Given the user has the following rights:
#      | akeneo_referenceentity_record_create | true |
#    And the user creates a record of "designer" with:
#      | code               | labels |
#      | invalid/identifier | {}     |
#    Then the record validation error will be "This field may only contain letters, numbers and underscores."
#    And the user saves the record
#    And a validation message is displayed "This field may only contain letters, numbers and underscores."
