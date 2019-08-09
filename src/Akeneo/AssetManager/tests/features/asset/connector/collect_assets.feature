Feature: Connection to DAM systems
  In order to centralize the asset family assets that contributes to the good final product experience
  As a connector
  I want to collect assets of an asset family from a MDM or an ERP system

  @integration-back
  Scenario: Collect an asset for a given asset family that exist in the ERP but not in the PIM and automatically link them to products
    Given an asset of the Frontview asset family existing in the ERP but not in the PIM having a rule template
    When the connector collects this asset from the ERP to synchronize it with the PIM
    Then the asset is created in the PIM with the information from the ERP
    And a job runs to automatically link it to products according to the rule template

  @integration-back
  Scenario: Collect some assets that exist in the ERP but not in the PIM
    Given an asset of the Frontview asset family existing in the ERP but not in the PIM
    When the connector collects this asset from the ERP to synchronize it with the PIM
    Then the asset is created in the PIM with the information from the ERP

  @integration-back
  Scenario: Collect an asset for a given asset family that already exists in the ERP and in the PIM but with different information
    Given an asset of the Brand asset family existing in the ERP and the PIM with different information
    When the connector collects this asset from the ERP to synchronize it with the PIM
    Then the asset is correctly synchronized in the PIM with the information from the ERP

  @integration-back
  Scenario: Notify an error when collecting a asset that has an invalid format
    Given the FrontView asset family with some assets
    When the connector collects a asset that has an invalid format
    Then the PIM notifies the connector about an error indicating that the asset has an invalid format

  @integration-back
  Scenario: Notify an error when collecting a asset whose data does not comply with the business rules
    Given the FrontView asset family with some assets
    When the connector collects a asset whose data does not comply with the business rules
    Then the PIM notifies the connector about an error indicating that the asset has data that does not comply with the business rules

  @integration-back
  Scenario: Collect assets for a given asset family from the ERP and automatically link them to products
    Given some assets of the Frontview asset family existing in the ERP but not in the PIM
    And some assets of the Frontview asset family existing in the ERP and in the PIM but with different information
    When the connector collects these assets from the ERP to synchronize them with the PIM
    Then the assets that existed only in the ERP are correctly created in the PIM
    And the assets existing both in the ERP and the PIM are correctly synchronized in the PIM with the information from the ERP
    And a job runs to automatically link to products the newly created asset according to the rule template

  @integration-back
  Scenario: Collect bunch of new assets for a given asset family from the ERP and automatically link them to products
    Given some assets of the Frontview asset family existing in the ERP but not in the PIM
    When the connector collects these assets from the ERP to synchronize them with the PIM
    Then a single job runs to automatically link to products the newly created asset according to the rule template

  @integration-back
  Scenario: Notify errors when collecting assets whose data do not comply with the business rules
    Given some assets of the Frontview asset family existing in the ERP but not in the PIM
    And some assets of the Frontview asset family existing in the ERP and in the PIM but with different information
    When the connector collects assets from the ERP among which some assets have data that do not comply with the business rules
    Then the PIM notifies the connector which assets have data that do not comply with the business rules and what are the errors

  @integration-back
  Scenario: Notify an error when collecting a number of assets exceeding the maximum number of assets in one request
    Given the Brand asset family with some assets
    When the connector collects a number of assets exceeding the maximum number of assets in one request
    Then the PIM notifies the connector that there were too many assets to collect in one request

  @integration-back
  Scenario: Delete an asset for a given asset family that exists in the PIM but not in the DAM anymore
    Given an asset of the Packshot asset family existing in the PIM
    When the connector deletes this asset
    Then the asset is not in the PIM anymore

  @integration-back
  Scenario: Notify an error when deleting an asset that does not exist in the PIM
    Given an asset of the Packshot asset family existing in the PIM
    When the connector deletes a wrong asset
    Then the PIM notifies the connector that the asset does not exist in the PIM
