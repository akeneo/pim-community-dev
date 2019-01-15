Feature: Connection to MDM or ERP systems
  In order to centralize the reference entity records that contribute to a good final product experience
  As a connector
  I want to collect the labels of the attribute options that compose some records, that are already stored in a MDM or an ERP system

  @integration-back
  Scenario: Collect a new attribute option of a single or multiple option attribute for a reference entity from the ERP
    Given the Brand reference entity reference entity existing both in the ERP and in the PIM
    And the Sales area attribute existing both in the ERP and in the PIM
    And the USA attribute option that only exists in the ERP but not in the PIM
    When the connector collects the USA attribute option of the Sales area Attribute of the Brand reference entity from the ERP to synchronize it with the PIM
    Then the USA attribute option of the Sales area attribute is added to the structure of the Brand reference entity in the PIM with the properties coming from the ERP

  @integration-back
  Scenario: Collect an existing attribute option of a single or multiple option attribute for a reference entity from the ERP
    Given the Brand reference entity reference entity existing both in the ERP and in the PIM
    And the Sales area attribute existing both in the ERP and in the PIM
    And the Australia attribute option of the Sales area attribute of the Brand reference entity in the ERP and in the PIM but with some unsynchronized properties
    When the connector collects the Australia attribute option of the Sales area Attribute of the Brand reference entity from the ERP to synchronize it with the PIM
    Then the Australia attribute option of the Sales area attribute is added to the structure of the Brand reference entity in the PIM with the properties coming from the ERP

  @integration-back
  Scenario: Notify an error when collecting an attribute option of a non-existent reference entity
    Given some reference entities
    When the connector collects an attribute option of a non-existent reference entity
    Then the PIM notifies the connector about an error indicating that the reference entity does not exist

  @integration-back
  Scenario: Notify an error when collecting an attribute option of a non-existent attribute
    Given the Brand reference entity
    And some attributes that structure the Brand reference entity
    When the connector collects an attribute option of a non-existent attribute
    Then the PIM notifies the connector about an error indicating that the attribute does not exist

  @integration-back
  Scenario: Notify an error when collecting an attribute option of an attribute that does not accept options
    Given the Brand reference entity
    And the Color attribute that structures the Brand reference entity and whose type is text
    When the connector collects an attribute option of an attribute that does not accept options
    Then the PIM notifies the connector about an error indicating that the attribute does accept options
