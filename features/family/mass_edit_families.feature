@javascript
Feature: Mass Edit Families
  In order to define common data between families
  As an administrator
  I need to be able to mass edit attributes and requirements of families

  Scenario: Successfully add many attributes with their requirements to many families
    Given the "footwear" catalog configuration
    And I am logged in as "Peter"
    And I am on the families grid
    # These families don't have attribute Length
    When I select rows Boots, Sneakers and Sandals
    And I press the "Bulk actions" button
    And I choose the "Set attributes requirements" operation
    And I add available attributes Length
    And I switch the attribute "length" requirement in channel "mobile"
    And I confirm mass edit
    And I wait for the "set_attribute_requirements" job to finish
    Then attribute "Length" should be required in family "boots" for channel "Mobile"
    And attribute "Length" should be required in family "sneakers" for channel "Mobile"
    And attribute "Length" should be required in family "sandals" for channel "Mobile"
    But attribute "Length" should be optional in family "boots" for channel "Tablet"
    And attribute "Length" should be optional in family "sneakers" for channel "Tablet"
    And attribute "Length" should be optional in family "sandals" for channel "Tablet"

  Scenario: Successfully set existing attribute requirements of many families
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    And I am on the families grid
    # These families already have attribute Name
    When I select rows Boots, Sneakers and Sandals
    And I press the "Bulk actions" button
    And I choose the "Set attributes requirements" operation
    And I display the Name attribute
    And I switch the attribute "name" requirement in channel "mobile"
    And I confirm mass edit
    And I wait for the "set_attribute_requirements" job to finish
    Then attribute "Name" should be required in family "boots" for channel "Mobile"
    And attribute "Name" should be required in family "sneakers" for channel "Mobile"
    And attribute "Name" should be required in family "sandals" for channel "Mobile"
    But attribute "Name" should be optional in family "boots" for channel "Tablet"
    And attribute "Name" should be optional in family "sneakers" for channel "Tablet"
    And attribute "Name" should be optional in family "sandals" for channel "Tablet"

  Scenario: Successfully return to the family page when cancelling family mass edit
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    And I am on the families grid
    When I select rows Boots and Sneakers
    And I press the "Bulk actions" button
    And I click on the cancel button of the mass edit
    Then I should be on the families page

  Scenario: Successfully mass edit more than 10 families
    Given the "default" catalog configuration
    And the following families:
      | code     |
      | first    |
      | second   |
      | third    |
      | fourth   |
      | fifth    |
      | sixth    |
      | seventh  |
      | eight    |
      | ninth    |
      | tenth    |
      | eleventh |
    And I am logged in as "Julia"
    And I am on the families grid
    And I select rows first, second, third, fourth, fifth, sixth, seventh, eight, ninth, tenth and eleventh
    And I press the "Bulk actions" button
    Then I should see the text "Select your action"

  @jira https://akeneo.atlassian.net/browse/PIM-4203
  Scenario: Successfully mass edit families after sorting by label
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    And I am on the families grid
    When I sort by "label" value ascending
    And I select rows Boots, Sneakers and Sandals
    And I press the "Bulk actions" button
    Then I should see the text "Select your action"

  @jira https://akeneo.atlassian.net/browse/PIM-6026
  Scenario: Successfully mass edit more families than the batch size limit
    Given the "default" catalog configuration
    And 110 generated families
    And the following attributes:
      | code | label-en_US | type             | group |
      | name | Name        | pim_catalog_text | other |
    And I am logged in as "Julia"
    And I am on the families grid
    And I select rows [family_1]
    When I select all entities
    And I press the "Bulk actions" button
    And I choose the "Set attributes requirements" operation
    And I display the Name attribute
    And I confirm mass edit
    And I wait for the "set_attribute_requirements" job to finish
    And I am on the dashboard page
    Then I should see notification:
      | type    | message                                              |
      | success | Mass edit Set family attribute requirements finished |

  Scenario: Successfully mass edit attribute requirements by attribute group
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    And I am on the families grid
    When I select rows Boots, Sneakers and Sandals
    And I press the "Bulk actions" button
    And I choose the "Set attributes requirements" operation
    And I add attributes by group "Marketing"
    And I should see attributes "Price, Rate of sale and Rating" in group "Marketing"
    And I confirm mass edit
    And I wait for the "set_attribute_requirements" job to finish
    Then attribute "price" should be optional in family "boots" for channel "Tablet"
    Then attributes "price, rate_sale and rating" should be optional in family "boots" for channel "Tablet"
    And attributes "price, rate_sale and rating" should be optional in family "sneakers" for channel "Tablet"
    And attributes "price, rate_sale and rating" should be optional in family "sandals" for channel "Tablet"
    And attributes "price, rate_sale and rating" should be optional in family "boots" for channel "Mobile"
    And attributes "price, rate_sale and rating" should be optional in family "sneakers" for channel "Mobile"
    And attributes "price, rate_sale and rating" should be optional in family "sandals" for channel "Mobile"

  @jira https://akeneo.atlassian.net/browse/PIM-6199
  Scenario: Successfully disable form when we are in validation step on mass edit families
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    And I am on the families grid
    When I select rows Boots, Sneakers and Sandals
    And I press the "Bulk actions" button
    And I choose the "Set attributes requirements" operation
    And I display the Name attribute
    And I switch the attribute "name" requirement in channel "mobile"
    And I move on to the next step
    Then I should not see the text "Add Attribute"
