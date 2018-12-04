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
