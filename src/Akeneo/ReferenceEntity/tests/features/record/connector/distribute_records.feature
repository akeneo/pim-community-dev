Feature: Connection to e-commerce platforms and marketplaces
  In order to benefit from the enrichment work of Julia on the reference entity records
  As a connector
  I want to distribute records of a reference entity to e-commerce platforms and marketplaces

  @integration-back
  Scenario: Get a single record of a reference entity
    Given the Kartell record for the Brand reference entity
    When the connector requests the Kartell record for the Brand reference entity
    Then the PIM returns the Kartell record of the Brand reference entity

  @integration-back
  Scenario: Notify an error when getting a non-existent record of a reference entity
    Given the Brand reference entity with some records
    When the connector requests for a non-existent record for the Brand reference entity
    Then the PIM notifies the connector about an error indicating that the record does not exist

  @integration-back
  Scenario: Notify an error when getting a record of a non-existent reference entity
    Given some reference entities with some records
    When the connector requests for a record for a non-existent reference entity
    Then the PIM notifies the connector about an error indicating that the reference entity does not exist

  @integration-back
  Scenario: Get all the records of a given reference entity
    Given 22 records for the Brand reference entity
    When the connector requests all records of the Brand reference entity
    Then the PIM returns the 22 records of the Brand reference entity

  @integration-back
  Scenario: Notify an error when getting all the records of a non-existent reference entity
    Given some reference entities with some records
    When the connector requests all the records for a non-existent reference entity
    Then the PIM notifies the connector about an error indicating that the reference entity does not exist
