Feature: Connection to e-commerce platforms and marketplaces
  In order to benefit from the enrichment work of Julia on the images linked to the reference entity records
  As a connector
  I want to distribute images of the records of a reference entity from the PIM to e-commerce platforms and marketplaces

  @integration-back
  Scenario: Download an image of a reference entity record
    Given the Kartell record of the Brand reference entity
    And the photo attribute enriched with an image
    When the connector requests to download the image of the photo attribute of the Kartell record
    Then the PIM returns the image of the photo attribute of the Kartell record

  @integration-back
  Scenario: Notify an error when requesting a non existent image
    Given the Kartell record of the Brand reference entity
    And the photo attribute enriched with an image
    When the connector requests to download the image of the photo attribute of the Kartell record giving the wrong code
    Then the PIM notifies the connector that the image does not exist

