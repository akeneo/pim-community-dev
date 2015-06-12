@javascript
Feature: Validate editing common attributes of multiple products
  In order to update multiple products with valid data
  As a product manager
  I need values to be validated when editing common attributes of products

  # what's tested here?
  # -----------------------------|-------------|---------------|-------------
  # TYPE                         | VALID VALID | INVALID VALUE | NULL VALUE
  # -----------------------------|-------------|---------------|-------------
  # pim_catalog_boolean          | done        | N/A           | N/A
  # pim_catalog_date             | done        | done          | done
  # pim_catalog_file             | done        | done          | done
  # pim_catalog_identifier       | N/A         | N/A           | N/A
  # pim_catalog_image            | done        | done          | done
  # pim_catalog_metric           | done        | done          | done
  # pim_catalog_multiselect      | done        | N/A           | done
  # pim_catalog_number           | done        | done          | done
  # pim_catalog_price_collection | done        | done          | done
  # pim_catalog_simpleselect     | done        | N/A           | done
  # pim_catalog_text             | done        | done          | done
  # pim_catalog_textarea         | done        | done          | done

  Background:
    Given a "footwear" catalog configuration
    And the following attributes:
      | code      | label     | type     | allowed_extensions | date_min   | max_characters |
      | available | Available | boolean  |                    |            |                |
      | date      | Date      | date     |                    | 2014-01-01 |                |
      | file      | File      | file     | gif                |            |                |
      | info      | Info      | textarea |                    |            | 25             |
    And the following family:
      | code          | attributes                                                                                                             |
      | master_family | sku, side_view, length, weather_conditions, number_in_stock, price, manufacturer, comment, available, date, file, info |
    And the following products:
      | sku      | family        |
      | boots    | master_family |
      | sneakers | master_family |
      | sandals  | master_family |
    And I am logged in as "Julia"
    And I am on the products page

  Scenario: Successfully mass edit a boolean attribute
    Given I mass-edit products boots and sneakers
    And I choose the "Edit common attributes" operation
    And I display the Available attribute
    And I check the "Available" switch
    And I move on to the next step
    And I wait for the "edit-common-attributes" mass-edit job to finish
    Then attribute Available of "boots" should be "true"
    And attribute Available of "sneakers" should be "true"
    When I am on the products page
    And I mass-edit products boots, sandals and sneakers
    And I choose the "Edit common attributes" operation
    And I display the Available attribute
    And I uncheck the "Available" switch
    And I move on to the next step
    And I wait for the "edit-common-attributes" mass-edit job to finish
    Then attribute Available of "boots" should be "false"
    And attribute Available of "sandals" should be "false"
    And attribute Available of "sneakers" should be "false"

  Scenario: Successfully mass edit a date attribute
    Given I mass-edit products boots and sneakers
    And I choose the "Edit common attributes" operation
    And I display the Date attribute
    And I change the Date to "2015-01-01"
    And I move on to the next step
    And I wait for the "edit-common-attributes" mass-edit job to finish
    Then attribute Date of "boots" should be "2015-01-01"
    And attribute Date of "sneakers" should be "2015-01-01"
    When I am on the products page
    And I mass-edit products boots, sandals and sneakers
    And I choose the "Edit common attributes" operation
    And I display the Date attribute
    And I move on to the next step
    And I wait for the "edit-common-attributes" mass-edit job to finish
    Then attribute Date of "boots" should be ""
    And attribute Date of "sandals" should be ""
    And attribute Date of "sneakers" should be ""
    When I am on the products page
    And I mass-edit products boots, sandals and sneakers
    And I choose the "Edit common attributes" operation
    And I display the Date attribute
    And I change the Date to "2013-01-01"
    And I move on to the next step
    # @TODO: fix this
    And I display the Date attribute
    Then I should see validation error "This date should be 2014-01-01 or after."
    And attribute Date of "boots" should be ""
    And attribute Date of "sandals" should be ""
    And attribute Date of "sneakers" should be ""

  Scenario: Successfully mass edit a file attribute
    Given I mass-edit products boots and sneakers
    And I choose the "Edit common attributes" operation
    And I display the File attribute
    And I attach file "bic-core-148.gif" to "File"
    And I move on to the next step
    Then the file "file" of products boots and sneakers should be "bic-core-148.gif"
    When I am on the products page
    And I mass-edit products boots, sandals and sneakers
    And I choose the "Edit common attributes" operation
    And I display the File attribute
    And I move on to the next step
    And I wait for the "edit-common-attributes" mass-edit job to finish
    Then the file "file" of products boots, sandals and sneakers should be ""
    When I am on the products page
    And I mass-edit products boots, sandals and sneakers
    And I choose the "Edit common attributes" operation
    And I display the File attribute
    And I attach file "akeneo.txt" to "File"
    And I move on to the next step
    Then I should see "The file extension is not allowed (allowed extensions: gif)."
    And the file "file" of products boots, sandals and sneakers should be ""

  Scenario: Successfully mass edit an image attribute
    Given I mass-edit products boots and sneakers
    And I choose the "Edit common attributes" operation
    And I display the Side view attribute
    And I attach file "SNKRS-1R.png" to "Side view"
    And I move on to the next step
    And I wait for the "edit-common-attributes" mass-edit job to finish
    Then the file "side_view" of products boots and sneakers should be "SNKRS-1R.png"
    When I am on the products page
    And I mass-edit products boots, sandals and sneakers
    And I choose the "Edit common attributes" operation
    And I display the Side view attribute
    And I move on to the next step
    And I wait for the "edit-common-attributes" mass-edit job to finish
    Then the file "side_view" of products boots, sandals and sneakers should be ""
    When I am on the products page
    And I mass-edit products boots, sandals and sneakers
    And I choose the "Edit common attributes" operation
    And I display the Side view attribute
    And I attach file "akeneo.txt" to "Side view"
    And I move on to the next step
    Then I should see "The file extension is not allowed (allowed extensions: gif, png, jpeg, jpg)."
    And the file "side_view" of products boots, sandals and sneakers should be ""

  Scenario: Successfully mass edit a metric attribute
    Given I mass-edit products boots and sneakers
    And I choose the "Edit common attributes" operation
    And I display the Length attribute
    And I change the Length to "10"
    And I move on to the next step
    And I wait for the "edit-common-attributes" mass-edit job to finish
    Then the metric "Length" of products boots and sneakers should be "10"
    When I am on the products page
    And I mass-edit products boots, sandals and sneakers
    And I choose the "Edit common attributes" operation
    And I display the Length attribute
    And I move on to the next step
    And I wait for the "edit-common-attributes" mass-edit job to finish
    Then the metric "Length" of products boots, sandals and sneakers should be ""
    When I am on the products page
    And I mass-edit products boots, sandals and sneakers
    And I choose the "Edit common attributes" operation
    And I display the Length attribute
    And I change the Length to "foo"
    And I move on to the next step
    # @TODO: fix this
    And I display the Length attribute
    Then I should see validation error "This value should be a valid number."
    Then the metric "Length" of products boots, sandals and sneakers should be ""

  Scenario: Successfully mass edit a multi select attribute
    Given I mass-edit products boots and sneakers
    And I choose the "Edit common attributes" operation
    And I display the Weather conditions attribute
    And I change the "Weather conditions" to "Dry, Hot"
    And I move on to the next step
    And I wait for the "edit-common-attributes" mass-edit job to finish
    Then the options "weather_conditions" of products boots and sneakers should be:
      | value |
      | dry   |
      | hot   |
    When I am on the products page
    And I mass-edit products boots, sandals and sneakers
    And I choose the "Edit common attributes" operation
    And I display the Weather conditions attribute
    And I move on to the next step
    And I wait for the "edit-common-attributes" mass-edit job to finish
    Then the options "weather_conditions" of products boots and sneakers should be:
      | value |
      |       |

  Scenario: Successfully mass edit a simple select attribute
    Given I mass-edit products boots and sneakers
    And I choose the "Edit common attributes" operation
    And I display the Manufacturer attribute
    And I change the "Manufacturer" to "Converse"
    And I move on to the next step
    And I wait for the "edit-common-attributes" mass-edit job to finish
    Then the option "manufacturer" of products boots and sneakers should be "Converse"
    When I am on the products page
    And I mass-edit products boots, sandals and sneakers
    And I choose the "Edit common attributes" operation
    And I display the Manufacturer attribute
    And I move on to the next step
    And I wait for the "edit-common-attributes" mass-edit job to finish
    Then the option "manufacturer" of products boots, sandals and sneakers should be ""

  Scenario: Successfully mass edit a number attribute
    Given I mass-edit products boots and sneakers
    And I choose the "Edit common attributes" operation
    And I display the Number in stock attribute
    And I change the "Number in stock" to "10"
    And I move on to the next step
    And I wait for the "edit-common-attributes" mass-edit job to finish
    Then attribute number_in_stock of "boots" should be "10"
    And attribute number_in_stock of "sneakers" should be "10"
    When I am on the products page
    And I mass-edit products boots, sandals and sneakers
    And I choose the "Edit common attributes" operation
    And I display the Number in stock attribute
    And I move on to the next step
    And I wait for the "edit-common-attributes" mass-edit job to finish
    Then attribute number_in_stock of "boots" should be ""
    And attribute number_in_stock of "sandals" should be ""
    And attribute number_in_stock of "sneakers" should be ""
    When I am on the products page
    And I mass-edit products boots, sandals and sneakers
    And I choose the "Edit common attributes" operation
    And I display the Number in stock attribute
    And I change the "Number in stock" to "-10"
    And I move on to the next step
    Then I should see validation error "This value should be 0 or more."
    And attribute number_in_stock of "boots" should be ""
    And attribute number_in_stock of "sandals" should be ""
    And attribute number_in_stock of "sneakers" should be ""

  Scenario: Successfully mass edit a price attribute
    Given I mass-edit products boots and sneakers
    And I choose the "Edit common attributes" operation
    And I display the Price attribute
    And I change the "$ Price" to "10"
    And I change the "€ Price" to "15"
    And I move on to the next step
    And I wait for the "edit-common-attributes" mass-edit job to finish
    Then the prices "Price" of products boots and sneakers should be:
      | amount | currency |
      | 10     | USD      |
      | 15     | EUR      |
    When I am on the products page
    And I mass-edit products boots, sandals and sneakers
    And I choose the "Edit common attributes" operation
    And I display the Price attribute
    And I move on to the next step
    And I wait for the "edit-common-attributes" mass-edit job to finish
    Then the prices "Price" of products boots, sandals and sneakers should be:
      | amount | currency |
      |        | USD      |
      |        | EUR      |
    When I am on the products page
    And I mass-edit products boots, sandals and sneakers
    And I choose the "Edit common attributes" operation
    And I display the Price attribute
    And I change the "$ Price" to "500"
    And I move on to the next step
    # @TODO: fix this
    And I display the Price attribute
    Then I should see validation error "This value should be 200 or less."
    Then the prices "Price" of products boots, sandals and sneakers should be:
      | amount | currency |
      |        | USD      |
      |        | EUR      |

  Scenario: Successfully mass edit a text attribute
    Given I mass-edit products boots and sneakers
    And I choose the "Edit common attributes" operation
    And I display the Comment attribute
    And I change the "Comment" to "Very nice comment"
    And I move on to the next step
    And I wait for the "edit-common-attributes" mass-edit job to finish
    Then attribute Comment of "boots" should be "Very nice comment"
    And attribute Comment of "sneakers" should be "Very nice comment"
    When I am on the products page
    And I mass-edit products boots, sandals and sneakers
    And I choose the "Edit common attributes" operation
    And I display the Comment attribute
    And I move on to the next step
    And I wait for the "edit-common-attributes" mass-edit job to finish
    Then attribute Comment of "boots" should be ""
    And attribute Comment of "sandals" should be ""
    And attribute Comment of "sneakers" should be ""
    When I am on the products page
    And I mass-edit products boots, sandals and sneakers
    And I choose the "Edit common attributes" operation
    And I display the Comment attribute
    And I change the Comment to an invalid value
    And I move on to the next step
    Then I should see validation error "This value is too long. It should have 255 characters or less."
    And attribute Comment of "boots" should be ""
    And attribute Comment of "sandals" should be ""
    And attribute Comment of "sneakers" should be ""

  Scenario: Successfully mass edit a textarea attribute
    Given I mass-edit products boots and sneakers
    And I choose the "Edit common attributes" operation
    And I display the Info attribute
    And I change the "Info" to "Very useful information"
    And I move on to the next step
    And I wait for the "edit-common-attributes" mass-edit job to finish
    Then attribute Info of "boots" should be "Very useful information"
    And attribute Info of "sneakers" should be "Very useful information"
    When I am on the products page
    And I mass-edit products boots, sandals and sneakers
    And I choose the "Edit common attributes" operation
    And I display the Info attribute
    And I move on to the next step
    And I wait for the "edit-common-attributes" mass-edit job to finish
    Then attribute Info of "boots" should be ""
    And attribute Info of "sandals" should be ""
    And attribute Info of "sneakers" should be ""
    When I am on the products page
    And I mass-edit products boots, sandals and sneakers
    And I choose the "Edit common attributes" operation
    And I display the Info attribute
    And I change the "Info" to "Extremely useful information"
    And I move on to the next step
    Then I should see validation error "This value is too long. It should have 25 characters or less."
    And attribute Info of "boots" should be ""
    And attribute Info of "sandals" should be ""
    And attribute Info of "sneakers" should be ""
