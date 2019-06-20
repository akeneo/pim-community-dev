Feature: Connection to e-commerce platforms and marketplaces
  In order to benefit from the enrichment work of Julia on the images linked to the reference entity records
  As a connector
  I want to distribute media files of the records of a reference entity from the PIM to e-commerce platforms and marketplaces

  @integration-back
  Scenario: Download a media file of a reference entity record
    Given the Kartell record of the Brand reference entity with a media file in an attribute value
    When the connector requests to download the media file of this attribute value
    Then the PIM returns the media file binary of this attribute value

  @integration-back
  Scenario: Notify an error when requesting a non existent image
    Given the Kartell record of the Brand reference entity with a media file in an attribute value
    When the connector requests to download a non existent media file
    Then the PIM notifies the connector that the media file does not exist

