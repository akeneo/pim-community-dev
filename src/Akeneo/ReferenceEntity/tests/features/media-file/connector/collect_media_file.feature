Feature: Connection to e-commerce platforms and marketplaces
  In order to benefit from the enrichment work of Julia on the images linked to the reference entity records
  As a connector
  I want to distribute images of the records of a reference entity from the PIM to e-commerce platforms and marketplaces

  @integration-back
  Scenario: Upload an image of a reference entity record
    Given the Brand reference entity with some records
    And the Kartell record of the Brand reference entity
    When the connector collects the image of a record from the DAM to synchronize it with the PIM
    Then the image is correctly uploaded inside the PIM
