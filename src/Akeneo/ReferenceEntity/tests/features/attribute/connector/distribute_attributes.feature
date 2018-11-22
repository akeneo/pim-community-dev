Feature: Connection to e-commerce platforms and marketplaces
  In order to properly structure the records of a given reference entity enriched in the PIM, inside my e-commerce platform and marketplace backends
  As a connector
  I want to know the structure of this given reference entity, ie the attributes that describe it

  @integration-back
  Scenario: Get all the attributes of a given reference entity
    Given the Brand reference entity described by 10 attributes
    When the connector requests all attributes of this entity
    Then the PIM returns the 10 attributes of the Brand reference entity

  @integration-back
  Scenario: Get an attribute of a given reference entity
    Given the Brand reference entity described by the Description attribute
    When the connector requests the Description attribute of the Brand reference entity
    Then the PIM returns the Description attribute
