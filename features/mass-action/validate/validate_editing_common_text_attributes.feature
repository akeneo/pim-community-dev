@javascript
Feature: Validate editing common text attributes of multiple products
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
