Feature: Connection to MDM or ERP systems
  In order to centralize the asset family assets that contribute to a good final product experience
  As a connector
  I want to collect the labels of the attribute options that compose some assets, that are already stored in a MDM or an ERP system

  @integration-back
  Scenario: Collect a new attribute option of a single or multiple option attribute for an asset family from the ERP
    Given the Brand asset family asset family existing both in the ERP and in the PIM
    And the Sales area attribute existing both in the ERP and in the PIM
    And the USA attribute option that only exists in the ERP but not in the PIM
    When the connector collects the USA attribute option of the Sales area Attribute of the Brand asset family from the ERP to synchronize it with the PIM
    Then the USA attribute option of the Sales area attribute is added to the structure of the Brand asset family in the PIM with the properties coming from the ERP

  @integration-back
  Scenario: Collect an existing attribute option of a single or multiple option attribute for an asset family from the ERP
    Given the Brand asset family asset family existing both in the ERP and in the PIM
    And the Sales area attribute existing both in the ERP and in the PIM
    And the Australia attribute option of the Sales area attribute of the Brand asset family in the ERP and in the PIM but with some unsynchronized properties
    When the connector collects the Australia attribute option of the Sales area Attribute of the Brand asset family from the ERP to synchronize it with the PIM
    Then the Australia attribute option of the Sales area attribute is added to the structure of the Brand asset family in the PIM with the properties coming from the ERP

  @integration-back
  Scenario: Notify an error when collecting an attribute option of a non-existent asset family
    Given some asset families
    When the connector collects an attribute option of a non-existent asset family
    Then the PIM notifies the connector about an error indicating that the asset family does not exist

  @integration-back
  Scenario: Notify an error when collecting an attribute option of a non-existent attribute
    Given the Brand asset family
    And some attributes that structure the Brand asset family
    When the connector collects an attribute option of a non-existent attribute
    Then the PIM notifies the connector about an error indicating that the attribute does not exist

  @integration-back
  Scenario: Notify an error when collecting an attribute option of an attribute that does not accept options
    Given the Brand asset family
    And the Color attribute that structures the Brand asset family and whose type is text
    When the connector collects an attribute option of an attribute that does not accept options
    Then the PIM notifies the connector about an error indicating that the attribute does accept options

  @integration-back
  Scenario: Notify an error when collecting a new attribute option with an invalid format for a given option attribute
    Given the Brand asset family asset family existing both in the ERP and in the PIM
    And the Sales area attribute existing both in the ERP and in the PIM
    And the USA attribute option that only exists in the ERP but not in the PIM
    When the connector collects the USA attribute option with an invalid format
    Then the PIM notifies the connector about an error indicating that the attribute option has an invalid format

  @integration-back
  Scenario: Notify an error when collecting an existing attribute option with an invalid format for a given option attribute
    Given the Brand asset family asset family existing both in the ERP and in the PIM
    And the Sales area attribute existing both in the ERP and in the PIM
    And the Australia attribute option of the Sales area attribute of the Brand asset family in the ERP and in the PIM but with some unsynchronized properties
    When the connector collects the Australia attribute option with an invalid format
    Then the PIM notifies the connector about an error indicating that the attribute option has an invalid format

  @integration-back
  Scenario: Notify an error when collecting a new attribute option whose data does not comply with the business rules
    Given the Brand asset family asset family existing both in the ERP and in the PIM
    And the Sales area attribute existing both in the ERP and in the PIM
    And the USA attribute option that only exists in the ERP but not in the PIM
    When the connector collects the USA attribute option whose data does not comply with the business rules
    Then the PIM notifies the connector about an error indicating that the option attribute has data that does not comply with the business rules

  @integration-back
  Scenario: Notify an error when collecting an existing attribute whose data does not comply with the business rules
    Given the Brand asset family asset family existing both in the ERP and in the PIM
    And the Sales area attribute existing both in the ERP and in the PIM
    And the Australia attribute option of the Sales area attribute of the Brand asset family in the ERP and in the PIM but with some unsynchronized properties
    When the connector collects the Australia attribute option whose data does not comply with the business rules
    Then the PIM notifies the connector about an error indicating that the option attribute has data that does not comply with the business rules

