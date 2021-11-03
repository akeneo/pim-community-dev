Feature: Connection to MDM or ERP systems
  In order to centralize the reference entity records that contributes to the good final product experience
  As a connector
  I want to collect records of a reference entity from a MDM or an ERP system

  @integration-back
  Scenario: Collect a record for a given reference entity that exist in the ERP but not in the PIM
    Given a record of the Brand reference entity existing in the ERP but not in the PIM
    When the connector collects this record from the ERP to synchronize it with the PIM
    Then the record is created in the PIM with the information from the ERP

  @integration-back
  Scenario: Collect a record for a given reference entity that already exists in the ERP and in the PIM but with different information
    Given a record of the Brand reference entity existing in the ERP and the PIM with different information
    When the connector collects this record from the ERP to synchronize it with the PIM
    Then the record is correctly synchronized in the PIM with the information from the ERP

  @integration-back
  Scenario: Notify an error when collecting a record that has an invalid format
    Given the Brand reference entity with some records
    When the connector collects a record that has an invalid format
    Then the PIM notifies the connector about an error indicating that the record has an invalid format

  @integration-back
  Scenario: Notify an error when collecting a record whose data does not comply with the business rules
    Given the Brand reference entity with some records
    When the connector collects a record whose data does not comply with the business rules
    Then the PIM notifies the connector about an error indicating that the record has data that does not comply with the business rules

  @integration-back
  Scenario: Collect records for a given reference entity from the ERP
    Given some records of the Brand reference entity existing in the ERP but not in the PIM
    And some records of the Brand reference entity existing in the ERP and in the PIM but with different information
    When the connector collects these records from the ERP to synchronize them with the PIM
    Then the records that existed only in the ERP are correctly created in the PIM
    And the records existing both in the ERP and the PIM are correctly synchronized in the PIM with the information from the ERP

  @integration-back
  Scenario: Notify errors when collecting records whose data do not comply with the business rules
    Given some records of the Brand reference entity existing in the ERP but not in the PIM
    And some records of the Brand reference entity existing in the ERP and in the PIM but with different information
    When the connector collects records from the ERP among which some records have data that do not comply with the business rules
    Then the PIM notifies the connector which records have data that do not comply with the business rules and what are the errors

  @integration-back
  Scenario: Notify an error when collecting a number of records exceeding the maximum number of records in one request
    Given the Brand reference entity with some records
    When the connector collects a number of records exceeding the maximum number of records in one request
    Then the PIM notifies the connector that there were too many records to collect in one request

  @integration-back
  Scenario: Notify an error when collecting a record for a given reference entity that exist in the ERP but not in the PIM
    Given a record of the Brand reference entity existing in the ERP but not in the PIM
    When the connector collects this record from the ERP to synchronize it with the PIM without permission
    Then the PIM notifies the connector about missing permissions for creating record
