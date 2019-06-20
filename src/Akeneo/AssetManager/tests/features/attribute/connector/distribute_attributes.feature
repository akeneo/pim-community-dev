Feature: Connection to e-commerce platforms and marketplaces
  In order to know how to interpret the assets attribute values of the asset families enriched in the PIM
  As a connector
  I want to distribute the structure of the asset families that are in the PIM

  @integration-back
  Scenario: Distribute the structure of a given asset family
    Given 6 attributes that structure the Brand asset family in the PIM
    When the connector requests the structure of the Brand asset family from the PIM
    Then the PIM returns the 6 attributes of the Brand asset family

  @integration-back
  Scenario: Notify an error when collecting the structure of a non-existent asset family
    Given some asset families with some attributes
    When the connector requests the structure of a non-existent asset family
    Then the PIM notifies the connector about an error indicating that the asset family does not exist

  @integration-back
  Scenario: Distribute a given attribute of a given asset family
    Given the Brand asset family
    And the Description attribute that is part of the structure of the Brand asset family
    When the connector requests the Description attribute of the Brand asset family
    Then the PIM returns the Description reference attribute

  @integration-back
  Scenario: Notify an error when collecting a given attribute of a non-existent asset family
    Given some asset families with some attributes
    When the connector requests a given attribute of a non-existent asset family
    Then the PIM notifies the connector about an error indicating that the asset family does not exist

  @integration-back
  Scenario: Notify an error when collecting a non-existent attribute of a given asset family
    Given the Brand asset family with some attributes
    When the connector requests a non-existent attribute of a given asset family
    Then the PIM notifies the connector about an error indicating that the attribute does not exist for the Brand asset family
