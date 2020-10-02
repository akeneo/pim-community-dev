Feature: Connection to e-commerce platforms and marketplaces
  In order to benefit from the enrichment work of Julia on the asset family assets
  As a connector
  I want to distribute assets of an asset family from the PIM to e-commerce platforms and marketplaces

  @integration-back
  Scenario: Get a single asset of an asset family
    Given the Kartell asset for the Brand asset family
    When the connector requests the Kartell asset for the Brand asset family
    Then the PIM returns the Kartell asset of the Brand asset family

  @integration-back
  Scenario: Notify an error when getting a non-existent asset of an asset family
    Given the Brand asset family with some assets
    When the connector requests for a non-existent asset for the Brand asset family
    Then the PIM notifies the connector about an error indicating that the asset does not exist

  @integration-back
  Scenario: Notify an error when getting a asset of a non-existent asset family
    Given some asset families with some assets
    When the connector requests for a asset for a non-existent asset family
    Then the PIM notifies the connector about an error indicating that the asset family does not exist

  @integration-back
  Scenario: Notify an error when getting an asset of an existent asset family with the wrong case
    Given the Kartell asset for the Brand asset family
    When the connector requests for an asset of an existent asset family with the wrong case
    Then the PIM notifies the connector about an error indicating that the asset family does not exist

  @integration-back
  Scenario: Get all the assets of a given asset family
    Given 7 assets for the Brand asset family
    When the connector requests all assets of the Brand asset family
    Then the PIM returns the 7 assets of the Brand asset family

  @integration-back
  Scenario: Notify an error when getting all the assets of a non-existent asset family
    Given some asset families with some assets
    When the connector requests all the assets for a non-existent asset family
    Then the PIM notifies the connector about an error indicating that the asset family does not exist

  @integration-back
  Scenario: Get the assets of an asset family with the information of a provided channel
    Given 3 assets for the Brand asset family with filled attribute values for the Ecommerce and the Tablet channels
    When the connector requests all assets of the Brand asset family with the information of the Ecommerce channel
    Then the PIM returns 3 assets of the Brand asset family with only the information of the Ecommerce channel

  @integration-back
  Scenario: Notify about an error when getting the assets of an asset family with the information of a provided channel that does not exist
    Given the Brand asset family with some assets
    When the connector requests all assets of the Brand asset family with the information of a non-existent channel
    Then the PIM notifies the connector about an error indicating that the provided channel does not exist

  @integration-back
  Scenario: Get the assets of an asset family with there information in a provided locale
    Given 3 assets for the Brand asset family with filled attribute values for the English and the French locales
    When the connector requests all assets of the Brand asset family with the information in English
    Then the PIM returns 3 assets of the Brand asset family with the information in English only

  @integration-back
  Scenario: Notify about an error when getting the assets of an asset family with the information of a provided locale that does not exist
    Given the Brand asset family with some assets
    When the connector requests all assets of the Brand asset family with the information of a provided locale that does not exist
    Then the PIM notifies the connector about an error indicating that the provided locale does not exist

  @integration-back
  Scenario: Get all the complete assets of a given asset family for a provided channel and provided locales
    Given 2 assets for the Brand asset family on the Ecommerce channel that are incomplete for the French locale but complete for the English locale
    And 2 assets for the Brand asset family on the Ecommerce channel that are complete for the French locale but that are incomplete for the English locale
    And 2 assets for the Brand asset family on the Ecommerce channel that are both complete for the French and the English locale
    When the connector requests all complete assets of the Brand asset family on the Ecommerce channel for the French and English locales
    Then the PIM returns the 2 complete assets of the Brand asset family on the Ecommerce channel for the French and English locales

  @integration-back
  Scenario: Notify about an error when getting all the complete assets of a given asset family for a provided channel that does not exist
    Given 2 assets for the Brand asset family on the Ecommerce channel that are incomplete for the French locale but complete for the English locale
    When the connector requests all complete assets of the Brand asset family on a channel that does not exist
    Then the PIM notifies the connector about an error indicating that the provided channel does not exist

  @integration-back
  Scenario: Notify about an error when getting all the complete assets of a given asset family for a provided locale is not activated for the provided channel
    Given 2 assets for the Brand asset family on the Ecommerce channel that are incomplete for the French locale but complete for the English locale
    When the connector requests all complete assets of the Brand asset family on the Ecommerce channel for a not activated locale
    Then the PIM notifies the connector about an error indicating that the provided channel does not exist

  @integration-back
  Scenario: Get the assets of an asset family that were updated since a provided date
    Given 2 assets for the Brand asset family that were last updated on the 10th of October 2018
    And 2 assets for the Brand asset family that were updated on the 15th of October 2018
    When the connector requests all assets of the Brand asset family updated since the 14th of October 2018
    Then the PIM returns the 2 assets of the Brand asset family that were updated on the 15th of October 2018

  @integration-back
  Scenario: Notify about an error when getting the assets of an asset family that were updated since a date that does not have the right format
    Given the Brand asset family with some assets
    When the connector requests assets that were updated since a date that does not have the right format
    Then the PIM notifies the connector about an error indicating that the date format is not the expected one
