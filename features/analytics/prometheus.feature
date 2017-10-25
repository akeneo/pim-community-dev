Feature: Access pim metrics in a Prometheus format
  In order to graph the data usage
  As an administrator
  I need to access Prometheus metrics

  Scenario: Successfully display metrics in a Prometheus format
    Given a "footwear" catalog configuration
    When I am on "/analytics/prometheus"
    Then the response content type should be "text/plain; version=0.0.4; charset=UTF-8"
    And the response should contain "TYPE pim_channels_total gauge"
    And the response should contain "pim_channels_total 2"
    And the response should contain "TYPE pim_products_total gauge"
    And the response should contain "pim_products_total 0"
    And the response should contain "TYPE pim_families_total gauge"
    And the response should contain "pim_families_total 5"
    And the response should contain "TYPE pim_locales_total gauge"
    And the response should contain "pim_locales_total{status=\"active\"} 1"
    And the response should contain "pim_locales_total{status=\"inactive\"} 209"
    And the response should contain "TYPE pim_users_total gauge"
    And the response should contain "pim_users_total{status=\"enabled\"} 6"
    And the response should contain "pim_users_total{status=\"disabled\"} 0"