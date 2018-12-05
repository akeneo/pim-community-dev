Feature: Connection to e-commerce platforms and marketplaces
  In order to know how to interpret the records attribute values of the reference entities enriched in the PIM
  As a connector
  I want to distribute the structure of the reference entities that are in the PIM

  @integration-back
  Scenario: Distribute the structure of a given reference entity
    Given 6 attributes that structure the Brand reference entity in the PIM
    When the connector requests the structure of the Brand reference entity from the PIM
    Then the PIM returns the 6 attributes of the Brand reference entity

  @integration-back
  Scenario: Notify an error when collecting the structure of a non-existent reference entity
    Given some reference entities with some attributes
    When the connector requests the structure of a non-existent reference entity
    Then the PIM notifies the connector about an error indicating that the reference entity does not exist
