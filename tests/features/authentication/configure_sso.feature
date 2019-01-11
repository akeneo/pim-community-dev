Feature: Configure the SSO
  In order to use the SSO on the PIM according to my Identity Provider needs
  As an administrator user
  I want to configure it with valid SAML properties

  @acceptance-back
  Scenario: Accept a valid configuration
    When I try to save a valid configuration
    Then I should have no validation errors

  @acceptance-back
  Scenario: Reject an empty configuration
    When I try to save an empty configuration
    Then I should have the following validation errors:
      | path                        | message                         |
      | code                        | This value should not be blank. |
      | identityProviderEntityId    | This value should not be blank. |
      | identityProviderSignOnUrl   | This value should not be blank. |
      | identityProviderLogoutUrl   | This value should not be blank. |
      | identityProviderCertificate | This value should not be blank. |
      | serviceProviderEntityId     | This value should not be blank. |
      | serviceProviderCertificate  | This value should not be blank. |
      | serviceProviderPrivateKey   | This value should not be blank. |


  @acceptance-back
  Scenario: Reject a configuration with invalid URLs
    When I try to save a configuration with invalid URLs
    Then I should have the following validation errors:
      | path                      | message                        |
      | identityProviderEntityId  | This value is not a valid URL. |
      | identityProviderSignOnUrl | This value is not a valid URL. |
      | identityProviderLogoutUrl | This value is not a valid URL. |
      | serviceProviderEntityId   | This value is not a valid URL. |

  @acceptance-back
  Scenario: Reject a configuration with invalid certificates
    When I try to save a configuration with invalid certificates
    Then I should have the following validation errors:
      | path                        | message                          |
      | identityProviderCertificate | This is not a valid certificate. |
      | serviceProviderCertificate  | This is not a valid certificate. |

  @acceptance-back
  Scenario: Reject a configuration with expired certificates
    When I try to save a configuration with an expired IdP certificate
    Then I should have the following validation errors:
      | path                        | message                       |
      | identityProviderCertificate | This certificate has expired. |

  @acceptance-back
  Scenario: Reject a configuration with private certificate not matching the public certificate
    When I try to save a configuration with invalid certificate and private key pair
    Then I should have the following validation errors:
      | path                       | message                                                  |
      | serviceProviderCertificate | Service Provider certificate and private key must match. |
      | serviceProviderPrivateKey  | Service Provider certificate and private key must match. |

