@javascript
Feature: Notify users after a project creation
  In order to start an enrichment project
  As a project creator
  I need to be notified when a project I can work on has been created

  Background:
    Given the "activity_manager" catalog configuration
    And the following attribute groups:
      | code      | label-en_US | group | type             |
      | marketing | Marketing   | other | pim_catalog_text |
      | technical | Technical   | other | pim_catalog_text |
      | other     | Other       | other | pim_catalog_text |
      | media     | Media       | other | pim_catalog_text |
    And the following attributes:
      | code         | label-en_US  | type                   | localizable | scopable | decimals_allowed | metric_family | default metric unit | useable_as_grid_filter | group     | allowed extensions |
      | sku          | SKU          | pim_catalog_identifier | 0           | 0        |                  |               |                     | 1                      | other     |                    |
      | name         | Name         | pim_catalog_text       | 1           | 0        |                  |               |                     | 1                      | marketing |                    |
      | description  | Description  | pim_catalog_text       | 1           | 1        |                  |               |                     | 0                      | marketing |                    |
      | size         | Size         | pim_catalog_text       | 1           | 0        |                  |               |                     | 1                      | marketing |                    |
      | weight       | Weight       | pim_catalog_metric     | 1           | 0        | 0                | Weight        | GRAM                | 1                      | technical |                    |
      | release_date | Release date | pim_catalog_date       | 1           | 0        |                  |               |                     | 1                      | other     |                    |
      | capacity     | Capacity     | pim_catalog_metric     | 0           | 0        | 0                | Binary        | GIGABYTE            | 1                      | technical |                    |
      | material     | Material     | pim_catalog_text       | 1           | 0        |                  |               |                     | 1                      | technical |                    |
      | picture      | Picture      | pim_catalog_image      | 0           | 1        |                  |               |                     | 0                      | media     | jpg                |
    And the following categories:
      | code       | label-en_US | parent  |
      | clothing   | Clothing    | default |
      | high_tech  | High-Tech   | default |
      | decoration | Decoration  | default |
    And the following product category accesses:
      | product category | user group          | access |
      | clothing         | Marketing           | edit   |
      | clothing         | Technical Clothing  | edit   |
      | clothing         | Technical High-Tech | none   |
      | clothing         | Read Only           | view   |
      | clothing         | Media manager       | edit   |
      | high_tech        | Marketing           | edit   |
      | high_tech        | Technical Clothing  | view   |
      | high_tech        | Technical High-Tech | edit   |
      | high_tech        | Read Only           | view   |
      | high_tech        | Media manager       | edit   |
      | decoration       | Marketing           | edit   |
      | decoration       | Technical Clothing  | none   |
      | decoration       | Technical High-Tech | none   |
      | decoration       | Read Only           | view   |
      | decoration       | Media manager       | edit   |
    And the following attribute group accesses:
      | attribute group | user group          | access | group | type             |
      | marketing       | Marketing           | edit   | other | pim_catalog_text |
      | marketing       | Technical Clothing  | view   | other | pim_catalog_text |
      | marketing       | Technical High-Tech | view   | other | pim_catalog_text |
      | marketing       | Read Only           | view   | other | pim_catalog_text |
      | marketing       | Media manager       | view   | other | pim_catalog_text |
      | technical       | Marketing           | view   | other | pim_catalog_text |
      | technical       | Technical Clothing  | edit   | other | pim_catalog_text |
      | technical       | Technical High-Tech | edit   | other | pim_catalog_text |
      | technical       | Read Only           | view   | other | pim_catalog_text |
      | technical       | Media manager       | none   | other | pim_catalog_text |
      | other           | Marketing           | edit   | other | pim_catalog_text |
      | other           | Technical Clothing  | edit   | other | pim_catalog_text |
      | other           | Technical High-Tech | edit   | other | pim_catalog_text |
      | other           | Read Only           | view   | other | pim_catalog_text |
      | other           | Media manager       | view   | other | pim_catalog_text |
      | media           | Marketing           | view   | other | pim_catalog_text |
      | media           | Technical Clothing  | view   | other | pim_catalog_text |
      | media           | Technical High-Tech | view   | other | pim_catalog_text |
      | media           | Read Only           | view   | other | pim_catalog_text |
      | media           | Media manager       | edit   | other | pim_catalog_text |
    And the following families:
      | code     | label-en_US | attributes                                             | requirements-ecommerce             | requirements-mobile                |
      | tshirt   | TShirts     | sku,name,description,size,weight,release_date,material | sku,name,size,description,material | sku,name,size,description,material |
      | usb_keys | USB Keys    | sku,name,description,size,weight,release_date,capacity | sku,name,size,description,capacity | sku,name,size,description,capacity |
      | posters  | Posters     | sku,name,description,size,release_date,picture         | sku,name,size,description,picture  | sku,name,size,description,picture  |
      | car      | Car         | sku,description                                        | sku,description                    | sku,description                    |
    And the following products:
      | sku                  | family   | categories         | name-en_US                | size-en_US | weight-en_US | weight-en_US-unit | release_date-en_US | release_date-fr_FR | material-en_US | capacity | capacity-unit |
      | tshirt-the-witcher-3 | tshirt   | clothing           | T-Shirt "The Witcher III" | M          | 5            | OUNCE             | 2015-06-20         | 2015-06-20         | cotton         |          |               |
      | tshirt-skyrim        | tshirt   | clothing           | T-Shirt "Skyrim"          | M          | 5            | OUNCE             |                    |                    |                |          |               |
      | tshirt-lcd           | tshirt   | clothing,high_tech | T-shirt LCD screen        | M          | 6            | OUNCE             | 2016-08-13         |                    |                |          |               |
      | usb-key-big          | usb_keys | high_tech          | USB Key Big 64Go          |            | 1            | OUNCE             | 2016-08-13         | 2016-10-13         |                |          |               |
      | usb-key-small        | usb_keys | high_tech          |                           |            | 1            | OUNCE             |                    |                    |                | 8        | GIGABYTE      |
      | poster-movie-contact | posters  | decoration         | Movie poster "Contact"    | A1         |              |                   |                    |                    |                |          |               |
      | my-awesome-car       | car      |                    | Awesome car               |            |              |                   |                    |                    |                |          |               |

  Scenario: Successfully notify users when creating a project on clothing
    Given I am logged in as "Julia"
    When I am on the products page
    And I filter by "category" with operator "" and value "clothing"
    And I show the filter "weight"
    # In order to remove the tshirt LCD which is in Clothing and High-Tech categories
    And I filter by "weight" with operator "<" and value "6 Ounce"
    And I click on the create project button
    When I fill in the following information in the popin:
      | project-label       | 2016 summer collection |
      | project-description | 2016 summer collection |
      | project-due-date    | 12/13/2018             |
    And I press the "Save" button
    Then I should be on the products page
    And I go on the last executed job resume of "project_calculation"
    And I wait for the "project_calculation" job to finish
    And I am on the homepage
    # Project creator must not be notified for project creation but only once the job is over.
    And I should have 1 new notification
    And I should see notification:
      | type    | message                      |
      | success | Project calculation finished |
    When I logout
    And I am logged in as "admin"
    # John has only read on categories and attribute groups. He can't edit, so he's not notified.
    Then I am on the homepage
    And I should see the text "Doe"
    And I should have 0 new notification
    When I logout
    And I am logged in as "Teddy"
    # Teddy doesn't see Clothing category.
    Then I am on the homepage
    And  I should see the text "Ferant"
    And I should have 0 new notification
    When I logout
    And I am logged in as "Kathy"
    # Kathy can edit Clothing category but she can't edit an attribute group of the selection.
    Then I am on the homepage
    And I should see the text "Kathy"
    And I should have 0 new notification
    When I logout
    And I am logged in as "Mary"
    # Mary can edit Clothing category and edit on Marketing and Others attribute groups.
    Then I should have 1 new notification
    And I should see notification:
      | type    | message                                                                                 |
      | success | You have new products to enrich for "2016 summer collection". Due date is "12/13/2018". |
    When I logout
    And I am logged in as "Claude"
    # Claude can edit Clothing category and edit on Technical and Others attribute groups.
    Then I should have 1 new notification
    And I should see notification:
      | type    | message                                                                                 |
      | success | You have new products to enrich for "2016 summer collection". Due date is "12/13/2018". |
    When I logout
    And I am logged in as "Marc"
    # Marc can edit Clothing category and edit on Technical and Others attribute groups.
    Then I should have 1 new notification
    And I should see notification:
      | type    | message                                                                                 |
      | success | You have new products to enrich for "2016 summer collection". Due date is "12/13/2018". |

  Scenario: Successfully notify users when creating a project with a product which is in two categories
    Given I am logged in as "Julia"
    When I am on the products page
    And I filter by "category" with operator "" and value "clothing"
    And I click on the create project button
    When I fill in the following information in the popin:
      | project-label       | 2016 summer collection |
      | project-description | 2016 summer collection |
      | project-due-date    | 12/13/2018             |
    And I press the "Save" button
    Then I should be on the products page
    And I go on the last executed job resume of "project_calculation"
    And I wait for the "project_calculation" job to finish
    And I am on the homepage
    # Project creator must not be notified for project creation but only once the job is over.
    And I should have 1 new notification
    And I should see notification:
      | type    | message                      |
      | success | Project calculation finished |
    When I logout
    And I am logged in as "admin"
    # John has only read on categories and attribute groups. He can't edit, so he's not notified.
    Then I am on the homepage
    And I should see the text "Doe"
    And I should have 0 new notification
    When I logout
    And I am logged in as "Kathy"
    # Kathy can edit Clothing and High-Tech categories but she can't edit an attribute group of the selection.
    Then I am on the homepage
    And I should see the text "Peneflame"
    And I should have 0 new notification
    When I logout
    And I am logged in as "Mary"
    # Mary can edit Clothing category and edit on Marketing and Others attribute groups.
    Then I should have 1 new notification
    And I should see notification:
      | type    | message                                                                                 |
      | success | You have new products to enrich for "2016 summer collection". Due date is "12/13/2018". |
    When I logout
    And I am logged in as "Claude"
    # Claude can edit Clothing category and edit on Technical and Others attribute groups.
    Then I should have 1 new notification
    And I should see notification:
      | type    | message                                                                                 |
      | success | You have new products to enrich for "2016 summer collection". Due date is "12/13/2018". |
    When I logout
    And I am logged in as "Marc"
    # Marc can edit Clothing category and edit on Technical and Others attribute groups.
    Then I should have 1 new notification
    And I should see notification:
      | type    | message                                                                                 |
      | success | You have new products to enrich for "2016 summer collection". Due date is "12/13/2018". |
    When I logout
    And I am logged in as "Teddy"
    # Teddy can edit High-Tech category and edit on Technical and Others attribute groups.
    Then I should have 1 new notification
    And I should see notification:
      | type    | message                                                                                 |
      | success | You have new products to enrich for "2016 summer collection". Due date is "12/13/2018". |

  Scenario: Successfully notify users when creating a project on high-tech
    Given I am logged in as "Marc"
    When I am on the products page
    And I filter by "category" with operator "" and value "high_tech"
    And I show the filter "weight"
    # In order to remove the tshirt LCD which is in Clothing and High-Tech categories
    And I filter by "weight" with operator "<" and value "6 Ounce"
    And I click on the create project button
    When I fill in the following information in the popin:
      | project-label       | 2016 summer collection |
      | project-description | 2016 summer collection |
      | project-due-date    | 12/13/2018             |
    And I press the "Save" button
    Then I should be on the products page
    And I go on the last executed job resume of "project_calculation"
    And I wait for the "project_calculation" job to finish
    And I am on the homepage
    # Project creator must not be notified for project creation but only once the job is over.
    And I should have 1 new notification
    And I should see notification:
      | type    | message                      |
      | success | Project calculation finished |
    When I logout
    And I am logged in as "admin"
    # John has only read on categories and attribute groups. He can't edit, so he's not notified.
    Then I am on the homepage
    And I should see the text "Doe"
    And I should have 0 new notification
    When I logout
    And I am logged in as "Claude"
    # Claude can only read High-Tech category.
    Then I am on the homepage
    And I should see the text "Yachifeur"
    And I should have 0 new notification
    When I logout
    And I am logged in as "Kathy"
    # Kathy can edit High-Tech category but she can't edit an attribute group of the selection.
    Then I am on the homepage
    And I should see the text "Peneflame"
    And I should have 0 new notification
    When I logout
    And I am logged in as "Mary"
    # Mary can edit High-Tech category and edit on Marketing and Others attribute groups.
    Then I should have 1 new notification
    And I should see notification:
      | type    | message                                                                                 |
      | success | You have new products to enrich for "2016 summer collection". Due date is "12/13/2018". |
    When I logout
    And I am logged in as "Teddy"
    # Teddy can edit High-Tech category and edit on Technical and Others attribute groups.
    Then I should have 1 new notification
    And I should see notification:
      | type    | message                                                                                 |
      | success | You have new products to enrich for "2016 summer collection". Due date is "12/13/2018". |
    When I logout
    And I am logged in as "Julia"
    # Julia has edit on all categories and attribute groups.
    Then I should have 1 new notification
    And I should see notification:
      | type    | message                                                                                 |
      | success | You have new products to enrich for "2016 summer collection". Due date is "12/13/2018". |

  Scenario: Successfully notify users when creating a project on decoration
    And I am logged in as "Julia"
    When I am on the products page
    And I filter by "category" with operator "" and value "decoration"
    And I click on the create project button
    When I fill in the following information in the popin:
      | project-label       | 2016 summer collection |
      | project-description | 2016 summer collection |
      | project-due-date    | 12/13/2018             |
    And I press the "Save" button
    Then I should be on the products page
    And I go on the last executed job resume of "project_calculation"
    And I wait for the "project_calculation" job to finish
    And I am on the homepage
    # Project creator must not be notified for project creation but only once the job is over.
    And I should have 1 new notification
    And I should see notification:
      | type    | message                      |
      | success | Project calculation finished |
    When I logout
    And I am logged in as "admin"
    # John has only read on categories and attribute groups. He can't edit, so he's not notified.
    Then I am on the homepage
    And I should see the text "Doe"
    And I should have 0 new notification
    When I logout
    And I am logged in as "Claude"
    # Claude can't see Decoration category.
    Then I am on the homepage
    And I should see the text "Yachifeur"
    And I should have 0 new notification
    When I logout
    And I am logged in as "Marc"
    # Marc can't see Decoration category.
    Then I am on the homepage
    And I should see the text "Assin"
    And I should have 0 new notification
    When I logout
    And I am logged in as "Teddy"
    # Teddy can't see Decoration category.
    Then I am on the homepage
    And I should see the text "Ferant"
    And I should have 0 new notification
    When I logout
    And I am logged in as "Mary"
    # Mary can edit Decoration category and edit Marketing attribute group.
    Then I should have 1 new notification
    And I should see notification:
      | type    | message                                                                                 |
      | success | You have new products to enrich for "2016 summer collection". Due date is "12/13/2018". |
    When I logout
    And I am logged in as "Kathy"
    # Kathy can edit Decoration category and edit Picture attribute group.
    Then I should have 1 new notification
    And I should see notification:
      | type    | message                                                                                 |
      | success | You have new products to enrich for "2016 summer collection". Due date is "12/13/2018". |

  Scenario: Successfully not notify users if the project is 100% done at project creation
    Given the following product values:
      | product        | attribute   | value                                | locale | scope     |
      | my-awesome-car | description | My awesome description for ecommerce | en_US  | ecommerce |
    And I am logged in as "admin"
    Then I am on the products page
    And I filter by "family" with operator "in list" and value "Car"
    And I click on the create project button
    And I fill in the following information in the popin:
      | project-label       | 2016 summer collection |
      | project-description | 2016 summer collection |
      | project-due-date    | 12/13/2018             |
    When I press the "Save" button
    And I should be on the products page
    And I go on the last executed job resume of "project_calculation"
    And I wait for the "project_calculation" job to finish
    When I logout
    And I am logged in as "Julia"
    Then I should see the text "Julia"
    And I should have 0 new notification

  Scenario: Successfully notify users if the project is not 100% done at project creation
    Given the following product values:
      | product        | attribute   | value | locale | scope     |
      | my-awesome-car | description |       | en_US  | ecommerce |
    When I am logged in as "admin"
    And I am on the products page
    And I filter by "family" with operator "in list" and value "Car"
    And I click on the create project button
    And I fill in the following information in the popin:
      | project-label       | 2016 summer collection |
      | project-description | 2016 summer collection |
      | project-due-date    | 12/13/2018             |
    And I press the "Save" button
    Then I should be on the products page
    And I go on the last executed job resume of "project_calculation"
    And I wait for the "project_calculation" job to finish
    When I logout
    And I am logged in as "Julia"
    Then I should see the text "Julia"
    Then I should have 1 new notification

  Scenario: Successfully notify users
    Given the following product values:
      | product        | attribute   | value | locale | scope     |
      | my-awesome-car | description |       | en_US  | ecommerce |
    When I am logged in as "admin"
    And I am on the products page
    And I filter by "family" with operator "in list" and value "Car"
    And I click on the create project button
    And I fill in the following information in the popin:
      | project-label       | 2016 summer collection |
      | project-description | 2016 summer collection |
      | project-due-date    | 12/13/2018             |
    And I press the "Save" button
    Then I should be on the products page
    And I go on the last executed job resume of "project_calculation"
    And I wait for the "project_calculation" job to finish
    And I am on the homepage
    # Is notified for the end of the project computation
    Then I should have 1 new notification
    Then I open the notification panel
    And I should see the text "Project calculation finished"
    When I logout
    And I am logged in as "Julia"
    And I am on the homepage
    # Is notified because she has products to enrich
    Then I should have 1 new notification
    Then I open the notification panel
    And I should see the text "You have new products to enrich for \"2016 summer collection\". Due date is \"12/13/2018\"."
    And I am on the products page
    And I am on the "my-awesome-car" product page
    And I fill in the following information:
      | Description | It is a car |
    Then I save the product
    And I am on the products page
    And I run computation of the project "2016-summer-collection-ecommerce-en-us"
    And I am on the homepage
    # Is notified because she finished her project
    Then I should have 2 new notification
    Then I open the notification panel
    And I should see the text "Congrats! You're 100% of done product done with \"2016 summer collection\"."
    And I am on the products page
    And I am on the "my-awesome-car" product page
    And I fill in the following information:
      | Description |  |
    Then I save the product
    And I am on the products page
    And I run computation of the project "2016-summer-collection-ecommerce-en-us"
    And I am on the homepage
    # Is not notified because she has already been notified for the project creation
    Then I should have 2 new notification
    And I am on the products page
    And I am on the "my-awesome-car" product page
    And I fill in the following information:
      | Description | It is a car |
    Then I save the product
    And I am on the products page
    And I run computation of the project "2016-summer-collection-ecommerce-en-us"
    And I am on the homepage
    # Is notified for project finished because the project was not to 100%
    Then I should have 3 new notification
    Then I open the notification panel
    And I should see the text "Congrats! You're 100% of done product done with \"2016 summer collection\"."
    And I run computation of the project "2016-summer-collection-ecommerce-en-us"
    And I am on the homepage
    # Is not notified because she was already at 100% just before and nothing changed between.
    Then I should have 3 new notification
