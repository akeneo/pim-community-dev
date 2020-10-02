Feature: Connection to MDM or ERP systems
  In order to centralize the asset family assets that contribute to a good final product experience
  As a connector
  I want to collect the general properties of the asset families that are already stored in a MDM or an ERP system

  @integration-back
  Scenario: Collect the properties of a given asset family that exist in the ERP but not in the PIM
    Given the Frontview asset family existing in the ERP but not in the PIM
    When the connector collects the properties of the Brand asset family from the ERP to synchronize it with the PIM
    Then the asset family is created with its properties in the PIM with the information from the ERP

  @integration-back
  Scenario: Collect the properties of a given asset family that already exists in the ERP and in the PIM but with different properties
    Given the Brand asset family existing in the ERP and the PIM with different properties
    When the connector collects the Brand asset family from the ERP to synchronize it with the PIM
    Then the properties of the asset family are correctly synchronized in the PIM with the information from the ERP

  @integration-back
  Scenario: Notify an error when collecting an asset family that has the same code with a wrong case
    Given the Brand asset family existing in the ERP and the PIM with different properties
    When the connector collects an asset family with a code that already exists with wrong case
    Then the PIM notifies the connector about an error indicating that the asset family has a code that already exist with wrong case

  @integration-back
  Scenario: Notify an error when collecting an asset family that has an invalid format
    Given some asset families
    When the connector collects an asset family that has an invalid format
    Then the PIM notifies the connector about an error indicating that the asset family has an invalid format

  @integration-back
  Scenario: Notify an error when collecting an asset family whose data does not comply with the business rules
    Given some asset families
    When the connector collects an asset family whose data does not comply with the business rules
    Then the PIM notifies the connector about an error indicating that the asset family has data that does not comply with the business rules

  @integration-back
  Scenario: Notify some errors when collecting an asset family whose transformation does not comply with the business rules
    Given some asset families with media file attributes
    When the connector collects an asset family whose transformations do not comply with the business rules
    Then the PIM notifies the connector about errors indicating that the asset family has transformations that do not comply with the business rules

  @integration-back
  Scenario: Notify some errors when collecting an asset family whose transformation have invalid operation parameters
    Given some asset families with media file attributes
    When the connector collects an asset family whose transformation have invalid operation parameters
    Then the PIM notifies the connector about errors indicating that the asset family has a transformation that does not have valid operation parameters

  @integration-back
  Scenario: Notify some errors when collecting an asset family whose naming convention does not comply with the business rules
    Given some asset families with media file attributes
    When the connector collects an asset family whose naming convention do not comply with the business rules
    Then the PIM notifies the connector about errors indicating that the asset family naming convention that do not comply with the business rules
