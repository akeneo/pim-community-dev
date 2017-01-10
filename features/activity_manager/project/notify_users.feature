@javascript
Feature: Notify users after a project creation
  In order to start an enrichment project
  As a project creator
  I need to be notified when a project I can work on has been created

  Background:
    Given the "activity_manager" catalog configuration
    And the following attribute groups:
      | code      | label-en_US |
      | marketing | Marketing   |
      | technical | Technical   |
      | other     | Other       |
      | media     | Media       |
    And the following attributes:
      | code         | label        | type       | localizable | scopable | decimals_allowed | metric_family | default metric unit | useable_as_grid_filter | group     | allowed extensions |
      | sku          | SKU          | identifier | no          | no       |                  |               |                     | yes                    | other     |                    |
      | name         | Name         | text       | yes         | no       |                  |               |                     | yes                    | marketing |                    |
      | description  | Description  | text       | yes         | yes      |                  |               |                     | no                     | marketing |                    |
      | size         | Size         | text       | yes         | no       |                  |               |                     | yes                    | marketing |                    |
      | weight       | Weight       | metric     | yes         | no       | no               | Weight        | GRAM                | yes                    | technical |                    |
      | release_date | Release date | date       | yes         | no       |                  |               |                     | yes                    | other     |                    |
      | capacity     | Capacity     | metric     | no          | no       | no               | Binary        | GIGABYTE            | yes                    | technical |                    |
      | material     | Material     | text       | yes         | no       |                  |               |                     | yes                    | technical |                    |
      | picture      | Picture      | image      | no          | yes      |                  |               |                     | no                     | media     | jpg                |
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
      | attribute group | user group          | access |
      | marketing       | Marketing           | edit   |
      | marketing       | Technical Clothing  | view   |
      | marketing       | Technical High-Tech | view   |
      | marketing       | Read Only           | view   |
      | marketing       | Media manager       | view   |
      | technical       | Marketing           | view   |
      | technical       | Technical Clothing  | edit   |
      | technical       | Technical High-Tech | edit   |
      | technical       | Read Only           | view   |
      | technical       | Media manager       | none   |
      | other           | Marketing           | edit   |
      | other           | Technical Clothing  | edit   |
      | other           | Technical High-Tech | edit   |
      | other           | Read Only           | view   |
      | other           | Media manager       | view   |
      | media           | Marketing           | view   |
      | media           | Technical Clothing  | view   |
      | media           | Technical High-Tech | view   |
      | media           | Read Only           | view   |
      | media           | Media manager       | edit   |
    And the following families:
      | code     | label-en_US | attributes                                                   | requirements-ecommerce                 | requirements-mobile                    |
      | tshirt   | TShirts     | sku, name, description, size, weight, release_date, material | sku, name, size, description, material | sku, name, size, description, material |
      | usb_keys | USB Keys    | sku, name, description, weight, release_date, capacity       | sku, name, size, description, capacity | sku, name, size, description, capacity |
      | posters  | Posters     | sku, name, description, size, release_date, picture          | sku, name, size, description, picture  | sku, name, size, description, picture  |
      | car      | Car         | sku, description                                             | sku, description                       | sku, description                       |
    And the following products:
      | sku                  | family   | categories         | name-en_US                | size-en_US | weight-en_US | weight-en_US-unit | release_date-en_US | release_date-fr_FR | material-en_US | capacity | capacity-unit |
      | tshirt-the-witcher-3 | tshirt   | clothing           | T-Shirt "The Witcher III" | M          | 5            | OUNCE             | 2015-06-20         | 2015-06-20         | cotton         |          |               |
      | tshirt-skyrim        | tshirt   | clothing           | T-Shirt "Skyrim"          | M          | 5            | OUNCE             |                    |                    |                |          |               |
      | tshirt-lcd           | tshirt   | clothing,high_tech | T-shirt LCD screen        | M          | 6            | OUNCE             | 2016-08-13         |                    |                |          |               |
      | usb-key-big          | usb_keys | high_tech          | USB Key Big 64Go          |            | 1            | OUNCE             | 2016-08-13         | 2016-10-13         |                |          |               |
      | usb-key-small        | usb_keys | high_tech          |                           |            | 1            | OUNCE             |                    |                    |                | 8        | GIGABYTE      |
      | poster-movie-contact | posters  | decoration         | Movie poster "Contact"    | A1         |              |                   |                    |                    |                |          |               |
      | my-awesome-car       | car      | decoration         | Awesome car               |            |              |                   |                    |                    |                |          |               |

  Scenario: Successfully notify users when creating a project on clothing
    Given I am logged in as "Julia"
    When I am on the products page
    And I filter by "category" with operator "" and value "clothing"
    And I show the filter "weight"
    # In order to remove the tshirt LCD which is in Clothing and High-Tech categories
    And I filter by "weight" with operator "<" and value "6 Ounce"
    And I open the view selector
    And I click on "Create project" action in the dropdown
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
      | type | message                                                                                     |
      | success  | You have new products to enrich for "2016 summer collection". Due date is "12/13/2018". |
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
    And I open the view selector
    And I click on "Create project" action in the dropdown
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
    And I open the view selector
    And I click on "Create project" action in the dropdown
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
    And I open the view selector
    And I click on "Create project" action in the dropdown
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
      | type     | message                                                                                 |
      | success  | You have new products to enrich for "2016 summer collection". Due date is "12/13/2018". |
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
    When I am logged in as "admin"
    And I am on the products page
    And I filter by "category" with operator "" and value "decoration"
    And I filter by "family" with operator "in list" and value "Car"
    And I open the view selector
    And I click on "Create project" action in the dropdown
    And I fill in the following information in the popin:
      | project-label       | 2016 summer collection |
      | project-description | 2016 summer collection |
      | project-due-date    | 12/13/2018             |
    And I press the "Save" button
    And I should be on the products page
    And I go on the last executed job resume of "project_calculation"
    And I wait for the "project_calculation" job to finish
    When I logout
    And I am logged in as "Julia"
    Then I should see the text "Julia"
    Then I should have 0 new notification

  Scenario: Successfully notify users if the project is not 100% done at project creation
    Given the following product values:
      | product        | attribute   | value                                | locale | scope     |
      | my-awesome-car | description |  | en_US  | ecommerce |
    When I am logged in as "admin"
    And I am on the products page
    And I filter by "category" with operator "" and value "decoration"
    And I filter by "family" with operator "in list" and value "Car"
    And I open the view selector
    And I click on "Create project" action in the dropdown
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
