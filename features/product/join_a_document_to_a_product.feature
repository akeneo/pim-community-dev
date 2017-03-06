Feature: Join a document to a product
  In order to join a document to a product
  As a product manager
  I need to be able to upload it and preview it

  Background:
    Given the "default" catalog configuration
    And the following attribute:
      | label-en_US | type             | allowed_extensions | group | code        |
      | Description | pim_catalog_file | txt                | other | description |
    And a "Car" product
    And the "Car" product has the "description" attribute
    And I am logged in as "Julia"
    And I am on the "Car" product page

  @javascript
  Scenario: Succesfully leave the document empty
    # Flash message is different from CE
    When I save the product
    Then I should see the flash message "Product working copy has been updated"
