Feature: Connection to e-commerce platforms and marketplaces
  In order to choose which reference entities will be used as dedicated pages inside my e-commerce webstore
  As a connector
  I want to distribute the properties of all the reference entities from the PIM into my e-commerce platform

  @integration-back
  Scenario: Get a reference entity
    Given the Brand reference entity
    When the connector requests the Brand reference entity
    Then the PIM returns the label and image properties Brand reference entity

  @integration-back
  Scenario: Notify an error when getting a non-existent reference entity
    Given some reference entities with some records
    When the connector requests a non-existent reference entity
    Then the PIM notifies the connector about an error indicating that the reference entity does not exist

  @integration-back
  Scenario: Get all the reference entities
    Given 7 reference entities in the PIM
    When the connector requests all reference entities of the PIM
    Then the PIM returns the label and image properties of the 7 reference entities of the PIM

  @integration-back
  Scenario: Notify an error when getting a reference entity without permission
    Given the Brand reference entity
    When the connector requests the Brand reference entity without permission
    Then the PIM notifies the connector about missing permissions for distributing this reference entity to the ERP

  @integration-back
  Scenario: Notify an error when getting all the reference entities without permission
    Given 7 reference entities in the PIM
    When the connector requests all reference entities of the PIM without permission
    Then the PIM notifies the connector about missing permissions for distributing the 7 reference entities of the PIM
