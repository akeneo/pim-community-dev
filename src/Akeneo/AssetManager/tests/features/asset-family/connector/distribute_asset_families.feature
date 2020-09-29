Feature: Connection to e-commerce platforms and marketplaces
  In order to choose which asset families will be used as dedicated pages inside my e-commerce webstore
  As a connector
  I want to distribute the properties of all the asset families from the PIM into my e-commerce platform

  @integration-back
  Scenario: Get an asset family
    Given the Brand asset family
    When the connector requests the Brand asset family
    Then the PIM returns the label, media_file properties and rule templates of Brand asset family

  @integration-back
  Scenario: Notify an error when getting a non-existent asset family
    Given some asset families with some assets
    When the connector requests a non-existent asset family
    Then the PIM notifies the connector about an error indicating that the asset family does not exist

  @integration-back
  Scenario: Notify an error when getting an asset family with wrong case
    Given the Brand asset family
    When the connector requests an asset family with wrong case
    Then the PIM notifies the connector about an error indicating that the asset family does not exist

  @integration-back
  Scenario: Get all the asset families
    Given 7 asset families in the PIM
    When the connector requests all asset families of the PIM
    Then the PIM returns the label and media_file properties of the 7 asset families of the PIM
