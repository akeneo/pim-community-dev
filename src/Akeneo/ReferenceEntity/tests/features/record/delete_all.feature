Feature: Delete all reference entity record
  In order to administrate records
  As a user
  I need to delete all records belonging to a refenrence entity

  @acceptance-back
  Scenario: Deleting all records of a reference entity
    Given two reference entities with two records each
    When the user deletes all the records from one reference entity
    Then there should be no records for this reference entity
    But there is still two records on the other reference entity

  @acceptance-back
  Scenario: Deleting all records of an unknown reference entity
    Given two reference entities with two records each
    When the user deletes all the records from an unknown entity
    And there is still two records for each reference entity

  @acceptance-front
  Scenario: Delete all reference entity records
    Given a valid reference entity
    And the following records for the reference entity "designer":
      | identifier        | code   | labels                        |
      | designer_starck_1 | starck | {"en_US": "Philippe Starck" } |
      | designer_coco_2   | coco   | {"en_US": "Coco"}             |
    And the user has the following rights:
      | akeneo_referenceentity_record_edit        | true |
      | akeneo_referenceentity_records_delete_all | true |
    And the user asks for the reference entity "designer"
    When the user deletes all the reference entity records
    Then the user should see the successfull deletion notification

  @acceptance-front
  Scenario: Error while deleting all reference entity records
    Given a valid reference entity
    And the following records for the reference entity "designer":
      | identifier        | code   | labels                        |
      | designer_starck_1 | starck | {"en_US": "Philippe Starck" } |
      | designer_coco_2   | coco   | {"en_US": "Coco"}             |
    And the user has the following rights:
      | akeneo_referenceentity_record_edit        | true |
      | akeneo_referenceentity_records_delete_all | true |
    And the user asks for the reference entity "designer"
    When the user cannot delete all the reference entity records
    Then the user should see the failed deletion notification

  @acceptance-front
  Scenario: Cannot delete all reference entity records without rights
    Given a valid reference entity
    And the following records for the reference entity "designer":
      | identifier        | code   | labels                        |
      | designer_starck_1 | starck | {"en_US": "Philippe Starck" } |
      | designer_coco_2   | coco   | {"en_US": "Coco"}             |
    And the user has the following rights:
      | akeneo_referenceentity_record_edit        | true  |
      | akeneo_referenceentity_records_delete_all | false |
    And the user asks for the reference entity "designer"
    Then the user should not see the delete all button
