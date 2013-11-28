@javascript
Feature: Well display navigation titles
  In order to have a well-formed title each the page
  As a user
  I need to be able to see title depending of the catalog page

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "admin"

  Scenario: Successfully display the attribute page titles
    Given I am on the attributes page
    Then I should see the title "Product attributes"
    When I am on the attribute creation page
    Then I should see the title "Product attributes | Create"
    When I edit the "size" attribute
    Then I should see the title "Product attributes Size | Edit"

  Scenario: Successfully display the channel page titles
    Given I am on the channels page
    Then I should see the title "Channels"
    When I am on the channel creation page
    Then I should see the title "Channels | Create"
    When I edit the "tablet" channel
    Then I should see the title "Channels Tablet | Edit"

  Scenario: Successfully display the currency page titles
    Given I am on the currencies page
    Then I should see the title "Currencies"

  Scenario: Successfully display the export page titles
    Given I am on the exports page
    Then I should see the title "Export management"
    When I click on the "footwear_product_export" row
    Then I should see the title "Export Footwear product export | Show"
    When I press the "Edit" button
    Then I should see the title "Export Footwear product export | Edit"

  Scenario: Successfully display the family page titles
    Given I am on the families page
    Then I should see the title "Families"
    When I am on the family creation page
    Then I should see the title "Families | Create"
    When I edit the "boots" family
    Then I should see the title "Families Boots | Edit"

  Scenario: Successfully display the attribute group page titles
    Given I am on the attribute group creation page
    Then I should see the title "Attribute groups | Create"
    When I edit the "info" attribute group
    Then I should see the title "Attribute groups Product information | Edit"

  Scenario: Successfully display the import page titles
    Given I am on the imports page
    Then I should see the title "Import management"
    When I click on the "footwear_product_import" row
    Then I should see the title "Import Footwear product import | Show"
    When I press the "Edit" button
    Then I should see the title "Import Footwear product import | Edit"

  Scenario: Successfully display the locale page titles
    Given I am on the locales page
    Then I should see the title "Locales"

  Scenario: Successfully display the product page titles
    Given a "sandals" product
    When I am on the products page
    Then I should see the title "Products"
    When I edit the "sandals" product
    Then I should see the title "Products sandals | Edit"

  Scenario: Successfully display the variant page titles
    Given I am on the variant groups page
    Then I should see the title "Variant groups"
    When I edit the "caterpillar_boots" variant group
    Then I should see the title "Variant groups Caterpillar boots | Edit"
