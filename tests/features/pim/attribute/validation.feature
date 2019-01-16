Feature: Validate attribute
  In order to create a valid attribute
  As a manager
  I need to validate the information provided

  @acceptance-back
  Scenario: Fail to create an attribute with an empty code
    When I create an attribute with a code "color%"
    Then the attribute should be invalid with message "Attribute code may contain only letters, numbers and underscores"

  @acceptance-back
  Scenario Outline: Fail to create an attribute with the reserved codes
    When I create an attribute with a code <code>
    Then the attribute should be invalid with message "This code is not available"

    Examples:
      | code             |
      | id               |
      | associationTypes |
      | categories       |
      | categoryId       |
      | completeness     |
      | enabled          |
      | family           |
      | groups           |
      | associations     |
      | products         |
      | scope            |
      | treeId           |
      | values           |
      | category         |
      | parent           |
      | label            |
      | entity_type      |

  @acceptance-back
  Scenario: Fail to create an attribute with the reserved code "*_groups"
    When I create an attribute with a code with a suffix "_groups"
    Then the attribute should be invalid with message "This code is not available"

  @acceptance-back
  Scenario: Fail to create an attribute with the reserved code "*_products"
    When I create an attribute with a code with a suffix "_products"
    Then the attribute should be invalid with message "This code is not available"

  @acceptance-back
  Scenario: Fail to create an attribute with an invalid regex
    When I create an attribute with an invalid regex
    Then the attribute should be invalid with message "This regular expression is not valid."

  @acceptance-back
  Scenario Outline: Fail to create an attribute with an empty field
    When I create an attribute with an empty <field>
    Then the attribute should be invalid with message "This value should not be blank."

    Examples:
      | field |
      | group |
      | type  |

  @acceptance-back
  Scenario: Fail to create an attribute with code > 255 characters
    When I create an attribute with a code > 255 characters
    Then the attribute should be invalid with message "This value is too long. It should have 255 characters or less."

  @acceptance-back
  Scenario: Fail to create a second identifier attribute
    When I create a second identifier attribute
    Then the attribute should be invalid with message "An identifier attribute already exists."
