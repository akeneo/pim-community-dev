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

  @integration-back
  Scenario: Distribute a given attribute of a given reference entity
    Given the Brand reference entity
    And the Description attribute that is part of the structure of the Brand reference entity
    When the connector requests the Description attribute of the Brand reference entity
    Then the PIM returns the Description reference attribute

  @integration-back
  Scenario: Notify an error when collecting a given attribute of a non-existent reference entity
    Given some reference entities with some attributes
    When the connector requests a given attribute of a non-existent reference entity
    Then the PIM notifies the connector about an error indicating that the reference entity does not exist

  @integration-back
  Scenario: Notify an error when collecting a non-existent attribute of a given reference entity
    Given the Brand reference entity with some attributes
    When the connector requests a non-existent attribute of a given reference entity
    Then the PIM notifies the connector about an error indicating that the attribute does not exist for the Brand reference entity

  @integration-back
  Scenario: Notify an error when distributing the structure of a given reference entity without permission
    Given 6 attributes that structure the Brand reference entity in the PIM
    When the connector requests the structure of the Brand reference entity from the PIM without permission
    Then the PIM notifies the connector about missing permissions for distributing 6 attributes of the Brand reference entity

  @integration-back
  Scenario: Notify an error when distributing a given attribute of a given reference entity without permission
    Given the Brand reference entity
    And the Description attribute that is part of the structure of the Brand reference entity
    When the connector requests the Description attribute of the Brand reference entity without permission
    Then the PIM notifies the connector about missing permissions for distributing the Description reference attribute
