Feature: Connection to e-commerce platforms and marketplaces
  In order to benefit from the enrichment work of Julia on the images linked to the asset family assets
  As a connector
  I want to distribute media files of the assets of an asset family from the PIM to e-commerce platforms and marketplaces

  @integration-back
  Scenario: Download a media file of an asset family asset
    Given the Kartell asset of the Brand asset family with a media file in an attribute value
    When the connector requests to download the media file of this attribute value
    Then the PIM returns the media file binary of this attribute value

  @integration-back
  Scenario: Notify an error when requesting a non existent image
    Given the Kartell asset of the Brand asset family with a media file in an attribute value
    When the connector requests to download a non existent media file
    Then the PIM notifies the connector that the media file does not exist

