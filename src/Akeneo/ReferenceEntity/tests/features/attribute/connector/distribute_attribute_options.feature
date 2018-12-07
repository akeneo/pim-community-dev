Feature: Connection to e-commerce platforms and marketplaces
  In order to properly interpret the single or multiple options attribute values into the records of a reference entity, inside my e-commerce platform and marketplace backends
  As a connector
  I want to distribute the options of a single or multiple options attribute of a reference entity

  @integration-back
  Scenario: Get an option of a single option attribute for a given reference entity
    Given the Brand reference entity
    And the Nationality single option attribute that is part of the structure of the Brand reference entity
    And the French option that is one of the options of the Nationality attribute
    When the connector requests the French option of the Nationality attribute for the Brand reference entity
    Then the PIM returns the French option

  @integration-back
  Scenario: Get an option of a multiple options attribute for a given reference entity
    Given the Brand reference entity
    And the Sales Area multiple options attribute that is part of the structure of the Brand reference entity
    And the Asia option that is one of the options of the Sales Area attribute
    When the connector requests the Asia option of the Sales Area attribute for the Brand reference entity
    Then the PIM returns the Asia option

  @integration-back
  Scenario: Notify an error when collecting an non-existent option for a given attribute for a given reference entity
    Given the Brand reference entity
    And the Nationality single option attribute that is part of the structure of the Brand reference entity but has no options yet
    When the connector requests a non-existent option for a given attribute for a given reference entity
    Then the PIM notifies the connector about an error indicating that the option is non existent for the Nationality attribute and the Brand reference entity
