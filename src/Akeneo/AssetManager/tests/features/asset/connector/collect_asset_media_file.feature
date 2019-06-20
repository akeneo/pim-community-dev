Feature: Connection to e-commerce platforms and marketplaces
  In order to benefit from the enrichment work of Julia on the images linked to the reference entity records
  As a connector
  I want to collect record media files of a reference entity from a MDM or an ERP system

  @integration-back
  Scenario: Enrich a record with a media file
    Given the Kartell record of the Brand reference entity without any media file
    When the connector collects a media file for the Kartell record from the DAM to synchronize it with the PIM
    Then the Kartell record is correctly synchronized with the uploaded media file

