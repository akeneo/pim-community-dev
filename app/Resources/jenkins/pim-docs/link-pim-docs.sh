#!/bin/bash

# clone pim-docs repository
git clone https://github.com/akeneo/pim-docs.git

# create symlink for Acme pim-docs bundles
ln -s pim-docs/src/Acme src/Acme


