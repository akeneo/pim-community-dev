Feature: Connection to MDM or ERP systems
  In order to centralize the reference entity records that contribute to a good final product experience
  As a connector
  I want to collect the general properties of the reference entities that are already stored in a MDM or an ERP system

  Scenario: Collect the properties of a given reference entity that exist in the ERP but not in the PIM
    Given the Brand reference entity existing in the ERP but not in the PIM
    When the connector collects the properties of the Brand reference entity from the ERP to synchronize it with the PIM
    Then the reference entity is created with its properties in the PIM with the information from the ERP
   
  Scenario: Collect the properties of a given reference entity that already exists in the ERP and in the PIM but with different properties
    Given the Brand reference entity existing in the ERP and the PIM with different properties
    When the connector collects the Brand reference entity from the ERP to synchronize it with the PIM
    Then the properties of the reference entity is correctly synchronized in the PIM with the information from the ERP
    
  Scenario: Notify an error when collecting a reference entity that has an invalid format
    Given some reference entities
    When the connector collects a reference entity that has an invalid format
    Then the PIM notifies the connector about an error indicating that the reference entity has an invalid format
    
  Scenario: Notify an error when collecting a reference entity whose data does not comply with the business rules
    Given some reference entities
    When the connector collects a reference entity whose data does not comply with the business rules
    Then the PIM notifies the connector about an error indicating that the reference entity has data that does not comply with the business rules
