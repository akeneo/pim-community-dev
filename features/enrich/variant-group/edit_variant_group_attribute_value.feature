@javascript
Feature: Editing attribute values of a variant group also updates products
  In order to easily edit common attributes of variant group products
  As a product manager
  I need to be able to change attribute values of a variant group

  # what's tested here?
  # --------------------------------|-------------|
  # TYPE                            | VALID VALUE |
  # --------------------------------|-------------|
  # pim_catalog_boolean             | done        |
  # pim_catalog_date                | done        |
  # pim_catalog_file                | done        |
  # pim_catalog_identifier          | N/A         |
  # pim_catalog_image               | done        |
  # pim_catalog_metric              | done        |
  # pim_catalog_multiselect         | done        |
  # pim_catalog_number              | done        |
  # pim_catalog_price_collection    | done        |
  # pim_catalog_simpleselect        | done        |
  # pim_catalog_text                | done        |
  # pim_catalog_textarea            | done        |

  Background:
    Given a "footwear" catalog configuration
    And the following variant group values:
      | group             | attribute          | value         | locale | scope  |
      | caterpillar_boots | destocking_date    | 2012-02-22    |        |        |
      | caterpillar_boots | length             | 10 CENTIMETER |        |        |
      | caterpillar_boots | weather_conditions | Dry           |        |        |
      | caterpillar_boots | number_in_stock    | 1900          |        |        |
      | caterpillar_boots | price              | 39.99 EUR     |        |        |
      | caterpillar_boots | rating             | 1             |        |        |
      | caterpillar_boots | name               | Old name      | en_US  |        |
      | caterpillar_boots | description        | A product.    | en_US  | tablet |
    And the following products:
      | sku  | groups            | color | size |
      | boot | caterpillar_boots | black | 40   |
    And the following attributes:
      | code                         | label-en_US           | label-fr_FR           | type                     | group     | allowed_extensions | localizable | available_locales |
      | technical_description        | Technical description | Description technique | pim_catalog_file         | media     | txt                | 0           |                   |
      | simple_select_local_specific | Simple                | Simple                | pim_catalog_simpleselect | marketing |                    | 1           | fr_FR,en_US       |
      | multi_select_local_specific  | Multi                 | Multi                 | pim_catalog_multiselect  | marketing |                    | 1           | fr_FR,en_US       |
    And I am logged in as "Julia"
    And I am on the "caterpillar_boots" variant group page
    And I visit the "Attributes" tab

  Scenario: Change a pim_catalog_boolean attribute of a variant group
    When I add available attributes Handmade
    And I visit the "Other" group
    And I check the "Handmade" switch
    And I save the variant group
    And I should see the flash message "Variant group successfully updated"
    And I should not see the text "There are unsaved changes."
    When I am on the "boot" product page
    And I visit the "Other" group
    Then the field Handmade should contain "on"

  Scenario: Change a pim_catalog_date attribute of a variant group
    Given I visit the "Other" group
    When I change the "Destocking date" to "01/01/2001"
    And I save the variant group
    And I should see the flash message "Variant group successfully updated"
    And I should not see the text "There are unsaved changes."
    When I am on the "boot" product page
    And I visit the "Other" group
    Then the field Destocking date should contain "01/01/2001"

  Scenario: Change a pim_catalog_metric attribute of a variant group
    When I change the "Length" to "5"
    And I save the variant group
    And I should see the flash message "Variant group successfully updated"
    And I should not see the text "There are unsaved changes."
    When I am on the "boot" product page
    Then the field Length should contain "5"

  Scenario: Change a pim_catalog_multiselect attribute of a variant group
    When I change the "Weather conditions" to "Wet, Cold"
    And I save the variant group
    And I should see the flash message "Variant group successfully updated"
    And I should not see the text "There are unsaved changes."
    When I am on the "boot" product page
    Then the field Weather conditions should contain "Wet, Cold"

  Scenario: Change a pim_catalog_number attribute of a variant group
    When I visit the "Other" group
    And I change the "Number in stock" to "8000"
    And I save the variant group
    And I should see the flash message "Variant group successfully updated"
    And I should not see the text "There are unsaved changes."
    When I am on the "boot" product page
    And I visit the "Other" group
    Then the field Number in stock should contain "8000"

  Scenario: Change a pim_catalog_price_collection attribute of a variant group
    When I visit the "Marketing" group
    And I change the "Price" to "89 EUR"
    And I save the variant group
    Then I should see the flash message "Variant group successfully updated"
    And I should not see the text "There are unsaved changes."
    When I am on the "boot" product page
    And I visit the "Marketing" group
    Then the field Price should contain "89"

  Scenario: Change a pim_catalog_simpleselect attribute of a variant group
    When I visit the "Marketing" group
    And I change the "Rating" to "5"
    And I save the variant group
    And I should see the flash message "Variant group successfully updated"
    And I should not see the text "There are unsaved changes."
    When I am on the "boot" product page
    And I visit the "Marketing" group
    Then the field Rating should contain "5 stars"

  Scenario: Change a pim_catalog_simpleselect locale specific attribute of a variant group
    Given I set the "English (United States), French (France)" locales to the "mobile" channel
    And I am on the "simple_select_local_specific" attribute page
    And I visit the "Values" tab
    And I create the following attribute options:
      | Code  |
      | red   |
      | blue  |
      | green |
    When I am on the "caterpillar_boots" variant group page
    And I visit the "Attributes" tab
    And I visit the "Marketing" group
    And I add available attributes Simple
    And I switch the scope to "mobile"
    And I change the "Simple" to "red"
    And I save the variant group
    And I switch the locale to "fr_FR"
    When I change the "Simple" to "blue"
    And I save the variant group
    Then I should not see the text "There are unsaved changes."
    When I am on the "boot" product page
    And I visit the "[marketing]" group
    And I switch the scope to "mobile"
    And I switch the locale to "en_US"
    Then I should see the text "[red]"
    When I switch the locale to "fr_FR"
    Then I should see the text "[blue]"

  Scenario: Change a pim_catalog_text attribute of a variant group
    When I change the "Name" to "In a galaxy far far away"
    And I save the variant group
    Then I should not see the text "There are unsaved changes."
    When I am on the "boot" product page
    Then the field Name should contain "In a galaxy far far away"

  Scenario: Change a pim_catalog_textarea attribute of a variant group
    When I change the "Description" to "The best boots!"
    And I save the variant group
    Then I should not see the text "There are unsaved changes."
    When I am on the "boot" product page
    Then the product "boot" should have the following values:
      | description-en_US-tablet | The best boots! |

  Scenario: Change a pim_catalog_image attribute of a variant group
    When I add available attributes Side view
    And I visit the "Media" group
    And I attach file "SNKRS-1R.png" to "Side view"
    And I save the variant group
    Then I should not see the text "There are unsaved changes."
    When I am on the "boot" product page
    And I visit the "Media" group
    Then I should see the text "SNKRS-1R.png"

  @jira https://akeneo.atlassian.net/browse/PIM-5335
  Scenario: Change a pim_catalog_image attribute of a variant group and ensure saving
    When I add available attributes Side view
    And I visit the "Media" group
    And I attach file "SNKRS-1R.png" to "Side view"
    And I save the variant group
    Then I should not see the text "There are unsaved changes."
    When I visit the "Products" tab
    And I uncheck the row "boot"
    And I save the variant group
    Then I should not see the text "There are unsaved changes."
    When I reload the page
    Then the row "boot" should not be checked

  Scenario: Change a pim_catalog_file attribute of a variant group
    When I add available attributes Technical description
    And I visit the "Media" group
    And I attach file "bic-core-148.txt" to "Technical description"
    And I save the variant group
    Then I should not see the text "There are unsaved changes."
    When I am on the "boot" product page
    And I visit the "Media" group
    Then I should see the text "bic-core-148.txt"

  @skip-nav
  Scenario: Successfully see a warning message on page exit
    When I add available attribute Handmade
    And I click on the Akeneo logo
    And I should see "You will lose changes to the Variant group if you leave the page." in popup
