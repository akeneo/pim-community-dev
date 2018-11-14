Feature: Connection to e-commerce platforms and marketplaces
  In order to administrate the records of the reference entities enriched in the PIM, inside my e-commerce platform and marketplace backends
  As a connector
  I want to know the name of all the reference entities that are in the PIM

  @integration-back
  Scenario: Get a reference entity
    Given the Brand reference entity
    When the connector requests the Brand reference entity
    Then the PIM returns the Brand reference entity
