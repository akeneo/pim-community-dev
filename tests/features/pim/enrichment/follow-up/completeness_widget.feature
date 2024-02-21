Feature: Completeness Widget
  In order to measure the progress on my catalog enrichment
  As a product manager
  I want to know the completeness of the catalog for all channels and locales

  @acceptance-back
  Scenario: Report the completeness for multiple channels
    Given the channel ecommerce with only complete products
    And the channel mobile with some incomplete products
    When the product manager asks for the completeness of the catalog
    Then the widget displays that the channel ecommerce is complete
    And the widget displays that the channel mobile is incomplete

  @acceptance-back
  Scenario: Report the completeness for multiple locales by channel
    Given the channel ecommerce with only complete products for French and English locale
    And the channel ecommerce with some incomplete products for Spanish locale
    When the product manager asks for the completeness of the catalog
    Then the channel ecommerce is complete for French and English locale
    And the channel ecommerce is incomplete for Spanish locale

