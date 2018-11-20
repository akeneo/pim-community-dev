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
    Given 7 records for the Brand reference entity
    When the connector requests all records of the Brand reference entity
    Then the PIM returns the 7 records of the Brand reference entity

  @integration-back
  Scenario: Notify an error when getting all the records of a non-existent reference entity
    Given some reference entities with some records
    When the connector requests all the records for a non-existent reference entity
    Then the PIM notifies the connector about an error indicating that the reference entity does not exist

  @integration-back
  Scenario: Get the records of a reference entity with the information of a provided channel
    Given 3 records for the Brand reference entity with filled attribute values for the Ecommerce and the Tablet channels
    When the connector requests all records of the Brand reference entity with the information of the Ecommerce channel
    Then the PIM returns 3 records of the Brand reference entity with only the information of the Ecommerce channel

  @integration-back
  Scenario: Notify about an error when getting the records of a reference entity with the information of a provided channel that does not exist
    Given the Brand reference entity with some records
    When the connector requests all records of the Brand reference entity with the information of a non-existent channel
    Then the PIM notifies the connector about an error indicating that the provided channel does not exist

  @integration-back
  Scenario: Get the records of a reference entity with there information in a provided locale
    Given 3 records for the Brand reference entity with filled attribute values for the English and the French locales
    And labels translated in the English and French locale
    When the connector requests all records of the Brand reference entity with the information in English
    Then the PIM returns 3 records of the Brand reference entity with the information in English only
    And the labels in English only

  @integration-back
  Scenario: Notify about an error when getting the records of a reference entity with the information of a provided locale that does not exist
    Given the Brand reference entity with some records
    When the connector requests all records of the Brand reference entity with the information of a provided locale that does not exist
    Then the PIM notifies the connector about an error indicating that the provided locale does not exist
