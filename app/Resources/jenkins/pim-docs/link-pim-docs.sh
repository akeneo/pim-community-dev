#!/bin/bash

# clone pim-docs repository
git clone https://github.com/akeneo/pim-docs.git

# create symlink for Acme pim-docs bundles
cd src
ln -s ../pim-docs/src/Acme Acme
cd ..

# update AppKernel
sed -i 's/PimEnrichBundle(),/PimEnrichBundle(),new Acme\\Bundle\\CatalogBundle\\AcmeCatalogBundle(),new Acme\\Bundle\\DemoConnectorBundle\\AcmeDemoConnectorBundle(),new Acme\\Bundle\\EnrichBundle\\AcmeEnrichBundle(),new Acme\\Bundle\\IcecatDemoBundle\\AcmeIcecatDemoBundle(),new Acme\\Bundle\\MyBundle\\AcmeMyBundle(),new Acme\\Bundle\\SpecificConnectorBundle\\AcmeSpecificConnectorBundle(),/' app/AppKernel.php
