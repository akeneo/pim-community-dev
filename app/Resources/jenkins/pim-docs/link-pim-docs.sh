#!/bin/bash

# clone custom-entity-bundle repository
git clone https://github.com/akeneo/CustomEntityBundle.git
git checkout --track -b pim-dev origin/pim-dev

# clone pim-docs repository
git clone https://github.com/akeneo/pim-docs.git

# create symlink for Acme pim-docs bundles
cd src
ln -s ../pim-docs/src/Acme Acme
cd Pim/Bundle
ln -s ../../../CustomEntityBundle CustomEntityBundle
cd ../../..

# update AppKernel
sed -i 's/PimEnrichBundle(),/PimEnrichBundle(),new Pim\\Bundle\\CustomEntityBundle\\PimCustomEntityBundle(),new Acme\\Bundle\\CatalogBundle\\AcmeCatalogBundle(),new Acme\\Bundle\\DemoConnectorBundle\\AcmeDemoConnectorBundle(),new Acme\\Bundle\\EnrichBundle\\AcmeEnrichBundle(),new Acme\\Bundle\\InstallerBundle\\AcmeInstallerBundle(),new Acme\\Bundle\\SpecificConnectorBundle\\AcmeSpecificConnectorBundle(),/' app/AppKernel.php

# update routing.yml
echo "pim_customentity:" >> app/config/routing.yml
echo "    prefix: /enrich" >> app/config/routing.yml
echo "    resource: \"@PimCustomEntityBundle/Resources/config/routing.yml\"" >> app/config/routing.yml

