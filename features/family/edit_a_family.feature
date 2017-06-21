@javascript
Feature: Edit a family
  In order to provide accurate information about a family
  As an administrator
  I need to be able to edit its code and the translations of its name

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Peter"

  Scenario: Successfully edit a family
    Given I am on the "Sneakers" family page
    Then I should see the Code field
    And the field Code should be disabled
    When I fill in the following information:
      | English (United States) | My family |
    And I save the family
    Then I should see "Family successfully updated"
    And I should see "My family"

  Scenario: Successfully edit a family
    And the following attributes:
      | label-en_US | type             | useable_as_grid_filter | group | code   |
      | String      | pim_catalog_text | 1                      | other | string |
    Given the following family:
      | code   | attributes      |
      | guitar | sku,name,string |
    And the following products:
      | sku      | family | name-en_US | string |
      | les-paul | guitar | Les Paul   | Elixir |
    And I am on the "guitar" family page
    When I fill in the following information:
      | Attribute used as label | String |
    And I save the family
    And I should not see the text "There are unsaved changes."
    When I am on the products page
    And I should see "Elixir"

  Scenario: Successfully set the translations of the name
    Given I am on the "Boots" family page
    When I fill in the following information:
      | English (United States) | NewBoots |
    And I save the family
    Then I should not see the text "There are unsaved changes."
    Then I should see "NewBoots"

  @skip-nav
  Scenario: Successfully display a dialog when we quit a page with unsaved changes
    Given I am on the "Boots" family page
    And I fill in the following information:
      | English (United States) | NewBoots |
    When I click on the Akeneo logo
    Then I should see "You will lose changes to the family if you leave the page." in popup

  Scenario: Successfully display a message when there are unsaved changes
    Given I am on the "Boots" family page
    And I fill in the following information:
      | English (United States) | NewBoots |
    Then I should see "There are unsaved changes."

  Scenario: Disable property fields when the user can't edit a family
    Given I am on the "Administrator" role page
    And I visit the "Permissions" tab
    And I revoke rights to resources Edit properties of a family
    And I save the role
    Then I should not see the text "There are unsaved changes."
    When I am on the "sneakers" family page
    Then the field Code should be disabled
    And the field Attribute used as label should be disabled
    And the field English (United States) should be disabled

  Scenario: Fail switching attribute requirements when the user can't edit a family attributes
    Given I am on the "Administrator" role page
    And I visit the "Permissions" tab
    And I revoke rights to resource Edit attributes of a family
    And I save the role
    Then I should not see the text "There are unsaved changes."
    When I am on the "sneakers" family page
    And I visit the "Attributes" tab
    Then attribute "name" should be required in channels mobile and tablet
    When I switch the attribute "name" requirement in channel "tablet"
    Then attribute "name" should be required in channels mobile and tablet

  Scenario: Fail removing attributes when the user can't edit family attributes
    Given I am on the "Administrator" role page
    And I visit the "Permissions" tab
    And I revoke rights to resource Edit attributes of a family
    And I save the role
    Then I should not see the text "There are unsaved changes."
    When I am on the "sneakers" family page
    And I visit the "Attributes" tab
    Then I should see attributes "SKU, Name, Manufacturer, Weather conditions, Description" in group "Product information"
    And I remove the "manufacturer" attribute
    Then I should see attributes "SKU, Name, Manufacturer, Weather conditions, Description" in group "Product information"

  Scenario: Fail adding attributes when the user can't edit family attributes
    Given I am on the "Administrator" role page
    And I visit the "Permissions" tab
    And I revoke rights to resource Edit attributes of a family
    And I save the role
    Then I should not see the text "There are unsaved changes."
    When I am on the "sneakers" family page
    And I visit the "Attributes" tab
    Then I should not see an "Available attributes" element
    And I should not see an "Available groups" element
