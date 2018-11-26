Feature: Connection to MDM or ERP systems
  In order to centralize the reference entity records that contributes to the good final product experience
  As a connector
  I want to collect the general properties of the reference entities that are already stored in a MDM or an ERP system
   
  Scenario: Collect the properties of reference entities from the ERP
    Given some reference entities existing in the ERP but not in the PIM
    And some reference entities existing in the ERP and in the PIM but with different properties
    When the connector collects these properties of the reference entities from the ERP to synchronize them with the PIM
    Then the reference entities that existed only in the ERP are correctly created with their properties in the PIM with the information from the ERP
    And the reference entities existing both in the ERP and the PIM are correctly synchronized in the PIM with the information from the ERP
   
  Scenario: Collect the properties of a given reference entity that exist in the ERP but not in the PIM
    Given the Brand reference entity existing in the ERP but not in the PIM
    When the connector collects the properties of the Brand reference entity from the ERP to synchronize it with the PIM
    Then the reference entity is created with its propeties in the PIM with the information from the ERP
   
  Scenario: Collect the properties of a given reference entity that already exists in the ERP and in the PIM but with different properties
    Given the Brand reference entity existing in the ERP and the PIM with different properties
    When the connector collects the Brand reference entity from the ERP to synchronize it with the PIM
    Then the properties of the reference entity is correctly synchronized in the PIM with the information from the ERP
    
  Scenario: Notify an error when collecting a reference entity property that is not in the reference entity format
    Given some reference entities
    When the connector collects a property that is not in the reference entity format
    Then the PIM notifies the connector about an error indicating that the property is not in the reference entity format
