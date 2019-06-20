Feature: Connection to e-commerce platforms and marketplaces
  In order to benefit from the enrichment work of Julia on the images linked to the asset family assets
  As a connector
  I want to collect asset media files of an asset family from a MDM or an ERP system

  @integration-back
  Scenario: Enrich a asset with a media file
    Given the Kartell asset of the Brand asset family without any media file
    When the connector collects a media file for the Kartell asset from the DAM to synchronize it with the PIM
    Then the Kartell asset is correctly synchronized with the uploaded media file

