SoapBundle
==========

Bundle add support for multiply SOAP controller classes work from one WSDL file.

Configuration in app/config/config.yml file:

    be_simple_soap:
        ...
        services:
            ...
            soap:
                namespace:     http://symfony.loc/Api/1.0/
                binding:       rpc-literal
                resource:      "%kernel.root_dir%/../src/Oro/Bundle/SoapBundle/Resources/config/soap.yml"
                resource_type: yml

Parameters:

 - **namespace** - namespase of WSDL file
 - **binding** - SOAP messaging format (rpc-literal)
 - **resource** - full path to yml config file
 - **resource_type** - for SoapBundle is 'yml'

Resource file consists of array of SOAP API controllers.

Example of yml resource file:

    classes:
      - Oro\Bundle\SearchBundle\Controller\Api\SoapController
      - Acme\Bundle\DemoBundle\Controller\Api\SoapController
