Feature: Connection to MDM or ERP systems
  In order to centralize the reference entity records that contribute to a good final product experience
  As a connector
  I want to collect the structure of the reference entities that are already stored in a MDM or an ERP system

  @integration-back
  Scenario: Collect an new attribute for a reference entity from the ERP
    Given the Color reference entity existing both in the ERP and in the PIM
    And the Main Color attribute that is only part of the structure of the Color reference entity in the ERP but not in the PIM
    When the connector collects the Main Color attribute of the Color reference entity from the ERP to synchronize it with the PIM
    Then the Main Color attribute is added to the structure of the Color reference entity in the PIM with the properties coming from the ERP
       
#  Scenario: Collect an existing attribute for a reference entity from the ERP
#    Given the Color reference entity existing both in the ERP and in the PIM
#    And the Main Color attribute that is both part of the structure of the Color reference entity in the ERP and in the PIM but with some unsynchronized properties
#    When the connector collects the Main Color attribute of the Color reference entity from the ERP to synchronize it with the PIM
#    Then the properties of the Main Color attribute are updated in the PIM with the properties coming from the ERP
#
#  Scenario: Notify an error when distributing an attribute of a non-existent reference entity
#    Given some reference entities
#    When the connector collects an attribute of a non-existent reference entity
#    Then the PIM notifies the connector about an error indicating that the reference entity does not exist
#
#  Scenario: Notify an error when distributing an attribute with an invalid format for a given reference entity
#    Given the Color reference entity
#    When the connector collects an attribute with an invalid format for the Color reference entity
#    Then the PIM notifies the connector about an error indicating that the attribute has an invalid format
#
#  Scenario: Notify an error when distributing an attribute whose data does not comply with the business rules for a given reference entity
#    Given the Color reference entity
#    When the connector collects an attribute whose data does not comply with the business rules for the Color reference entity
#    Then the PIM notifies the connector about an error indicating that the attribure has data that does not comply with the business rules
