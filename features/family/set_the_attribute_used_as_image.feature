@javascript
Feature: Set the attribute used as the main picture
  In order to let the user know which attribute is used as the main picture in the UI for each family
  As an administrator
  I need to be able to set the attribute used as the main picture

  Background:
    Given the "default" catalog configuration
    And the following attributes:
      | label-en_US | type              | group | code  |
      | Brand       | pim_catalog_text  | other | brand |
      | Image       | pim_catalog_image | other | image |
    And the following family:
      | code | attributes |
      | Bags | brand      |
    And I am logged in as "Peter"

  Scenario: Successfully show default value for attribute_as_image
    Given I am on the "Bags" family page
    Then I should see the text "Not selected"

  Scenario: Successfully change attribute_as_image
    Given I am on the "Bags" family page
    And I visit the "Attributes" tab
    And I add available attribute Image
    And I visit the "Properties" tab
    And I fill in the following information:
      | Attribute used as the main picture | Image |
    And I save the family
    And I should not see the text "There are unsaved changes."
    When I am on the "Bags" family page
    Then I should see "Image"

  Scenario: Successfully disallow removal of attribute_as_image from attributes
    Given I am on the "Bags" family page
    And I visit the "Attributes" tab
    And I add available attribute Image
    And I visit the "Properties" tab
    And I fill in the following information:
      | Attribute used as the main picture | Image |
    And I save the family
    And I should not see the text "There are unsaved changes."
    And I visit the "Attributes" tab
    When I remove the "image" attribute
    Then I should see attributes "Image" in group "Other"
    And I should not see the text "There are unsaved changes."
