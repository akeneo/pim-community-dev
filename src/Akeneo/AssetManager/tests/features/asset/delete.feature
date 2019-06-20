Feature: Delete one record
  In order to administrate records
  As a user
  I need to delete records

  @acceptance-back
  Scenario: Deleting a record
    Given a reference entity with one record
    When the user deletes the record
    Then there is no exception thrown
    And there is no violations errors
    And the record should not exist anymore

  @acceptance-back
  Scenario: Deleting a unknown record
    When the user tries to delete record that does not exist
    Then an exception is thrown

  @acceptance-front
  Scenario: Deleting a record
    Given a valid record
    And the user has the locale permission to edit the record
    And the user has the following rights:
      | akeneo_referenceentity_record_create | true |
      | akeneo_referenceentity_record_edit   | true |
      | akeneo_referenceentity_record_delete | true |
    When the user deletes the record
    Then the user should see a success message on the edit page

  @acceptance-front
  Scenario: Cannot delete a record without the rights
    Given a valid record
    And the user has the locale permission to edit the record
    And the user has the following rights:
      | akeneo_referenceentity_record_create | true |
      | akeneo_referenceentity_record_edit   | true  |
      | akeneo_referenceentity_record_delete | false |
    Then the user should not see the delete button
