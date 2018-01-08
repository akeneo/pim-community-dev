@javascript
Feature: Set the attribute used as label
  In order to let the user which attribute is the most accurate as the product title
  As an administrator
  I need to be able to set the attribute used as the label

  Background:
    Given the "default" catalog configuration
    And the following attributes:
      | label-en_US | type                 | group | code        | decimals_allowed | negative_allowed |
      | Brand       | pim_catalog_text     | other | brand       |                  |                  |
      | Model       | pim_catalog_text     | other | model       |                  |                  |
      | Size        | pim_catalog_number   | other | size        | 0                | 0                |
      | Description | pim_catalog_textarea | other | description |                  |                  |
    And the following family:
      | code | attributes                   |
      | Bags | brand,model,size,description |
    And I am logged in as "Peter"

  Scenario: Successfully set a family attribute as the family label
    Given I am on the "Bags" family page
    Then eligible attributes as label should be SKU, Brand and Model

  Scenario: Successfully set a family attribute as the family label
    Given I am on the "Bags" family page
    And I fill in the following information:
      | Attribute used as label | Brand |
    And I save the family
    And I should not see the text "There are unsaved changes."
    Then I am on the families page
    And I should see "Brand"

  Scenario: Successfully display the chosen attribute as the title of the product
    Given the attribute "Brand" has been chosen as the family "Bags" label
    And the following products:
      | sku      | family | brand |
      | bag-jean | Bags   | Levis |
    When I am on the "bag-jean" product page
    Then the title of the product should be "Levis"

  Scenario: Successfully display the id as the title of the product
    Given the following products:
      | sku      |
      | bag-jean |
    When I am on the "bag-jean" product page
    Then the title of the product should be "bag-jean"

  Scenario: Fail to remove an attribute that is used as the family label
    Given the attribute "Brand" has been chosen as the family "Bags" label
    When I am on the "Bags" family page
    And I visit the "Attributes" tab
    Then I should see attributes "Brand, Model, Size, Description" in group "Other"
    And I remove the "brand" attribute
    Then I should see the flash message "This attribute can not be removed because it is used as the label of the family"
    And I should see attributes "Brand" in group "Other"
