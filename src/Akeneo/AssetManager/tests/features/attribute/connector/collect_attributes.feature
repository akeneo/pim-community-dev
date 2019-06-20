Feature: Connection to MDM or ERP systems
  In order to centralize the asset family assets that contribute to a good final product experience
  As a connector
  I want to collect the structure of the asset families that are already stored in a MDM or an ERP system

  @integration-back
  Scenario: Collect a new text attribute for an asset family from the ERP
    Given the Color asset family existing both in the ERP and in the PIM
    And the Main Color attribute that is only part of the structure of the Color asset family in the ERP but not in the PIM
    When the connector collects this attribute from the ERP to synchronize it with the PIM
    Then the Main Color attribute is added to the structure of the Color asset family in the PIM with the properties coming from the ERP

  @integration-back
  Scenario: Collect a new image attribute for an asset family from the ERP
    Given the Designer asset family existing both in the ERP and in the PIM
    And the image attribute Portrait that is only part of the structure of the Designer asset family in the ERP but not in the PIM
    When the connector collects this attribute from the ERP to synchronize it with the PIM
    Then the Portrait attribute is added to the structure of the Designer asset family in the PIM with the properties coming from the ERP

  @integration-back
  Scenario: Collect a new asset attribute for an asset family from the ERP
    Given the Designer asset family existing both in the ERP and in the PIM
    And the asset attribute Country that is only part of the structure of the Designer asset family in the ERP but not in the PIM
    When the connector collects this attribute from the ERP to synchronize it with the PIM
    Then the Country attribute is added to the structure of the Designer asset family in the PIM with the properties coming from the ERP

  @integration-back
  Scenario: Collect a new option attribute for an asset family from the ERP
    Given the Designer asset family existing both in the ERP and in the PIM
    And the option attribute Birth Date that is only part of the structure of the Designer asset family in the ERP but not in the PIM
    When the connector collects this attribute from the ERP to synchronize it with the PIM
    Then the Birth Date attribute is added to the structure of the Designer asset family in the PIM with the properties coming from the ERP

  @integration-back
  Scenario: Collect a new url attribute for an asset family from the ERP
    Given the Designer asset family existing both in the ERP and in the PIM
    And the url attribute Preview that is only part of the structure of the Designer asset family in the ERP but not in the PIM
    When the connector collects this attribute from the ERP to synchronize it with the PIM
    Then the Preview attribute is added to the structure of the Designer asset family in the PIM with the properties coming from the ERP

  @integration-back
  Scenario: Collect an existing text type attribute for an asset family from the ERP
    Given the Color asset family existing both in the ERP and in the PIM
    And the Main Color attribute that is both part of the structure of the Color asset family in the ERP and in the PIM but with some unsynchronized properties
    When the connector collects this attribute from the ERP to synchronize it with the PIM
    Then the properties of the Main Color attribute are updated in the PIM with the properties coming from the ERP

  @integration-back
  Scenario: Collect an existing image type attribute for an asset family from the ERP
    Given the Designer asset family existing both in the ERP and in the PIM
    And the Portrait attribute that is both part of the structure of the Designer asset family in the ERP and in the PIM but with some unsynchronized properties
    When the connector collects this attribute from the ERP to synchronize it with the PIM
    Then the properties of the Portrait attribute are updated in the PIM with the properties coming from the ERP

  @integration-back
  Scenario: Collect an existing asset type attribute for an asset family from the ERP
    Given the Designer asset family existing both in the ERP and in the PIM
    And the Country attribute that is both part of the structure of the Designer asset family in the ERP and in the PIM but with some unsynchronized properties
    When the connector collects this attribute from the ERP to synchronize it with the PIM
    Then the properties of the Country attribute are updated in the PIM with the properties coming from the ERP

  @integration-back
  Scenario: Collect an existing option type attribute for an asset family from the ERP
    Given the Designer asset family existing both in the ERP and in the PIM
    And the option attribute Birth Date that is both part of the structure of the Designer asset family in the ERP and in the PIM but with some unsynchronized properties
    When the connector collects this attribute from the ERP to synchronize it with the PIM
    Then the properties of the Birth Date attribute are updated in the PIM with the properties coming from the ERP

  @integration-back
  Scenario: Collect an existing url type attribute for an asset family from the ERP
    Given the Designer asset family existing both in the ERP and in the PIM
    And the url attribute Preview that is both part of the structure of the Designer asset family in the ERP and in the PIM but with some unsynchronized properties
    When the connector collects this attribute from the ERP to synchronize it with the PIM
    Then the properties of the Preview attribute are updated in the PIM with the properties coming from the ERP

  @integration-back
  Scenario: Notify an error when collecting an attribute of a non-existent asset family
    Given some asset families
    When the connector collects an attribute of a non-existent asset family
    Then the PIM notifies the connector about an error indicating that the asset family does not exist

  @integration-back
  Scenario: Notify an error when collecting a new attribute with an invalid format for a given asset family
    Given the Color asset family existing both in the ERP and in the PIM
    And the Main Color attribute that is only part of the structure of the Color asset family in the ERP but not in the PIM
    When the connector collects the new Main color attribute with an invalid format
    Then the PIM notifies the connector about an error indicating that the attribute has an invalid format

  @integration-back
  Scenario: Notify an error when collecting an existing attribute with an invalid format for a given asset family
    Given the Color asset family existing both in the ERP and in the PIM
    And the Main Color attribute that is both part of the structure of the Color asset family in the ERP and in the PIM but with some unsynchronized properties
    When the connector collects the existing Main color attribute with an invalid format
    Then the PIM notifies the connector about an error indicating that the attribute has an invalid format

  @integration-back
  Scenario: Notify an error when collecting a new attribute whose data does not comply with the business rules
    Given the Color asset family existing both in the ERP and in the PIM
    And the Main Color attribute that is only part of the structure of the Color asset family in the ERP but not in the PIM
    When the connector collects the new Main Color attribute whose data does not comply with the business rules
    Then the PIM notifies the connector about an error indicating that the attribute has data that does not comply with the business rules

  @integration-back
  Scenario: Notify an error when collecting an existing attribute whose data does not comply with the business rules
    Given the Color asset family existing both in the ERP and in the PIM
    And the Main Color attribute that is both part of the structure of the Color asset family in the ERP and in the PIM but with some unsynchronized properties
    When the connector collects the existing Main Color attribute whose data does not comply with the business rules
    Then the PIM notifies the connector about an error indicating that the attribute has data that does not comply with the business rules
