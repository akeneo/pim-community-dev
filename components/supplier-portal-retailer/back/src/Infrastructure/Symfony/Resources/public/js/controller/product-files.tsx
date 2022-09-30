import React from 'react';
import {ReactController} from '@akeneo-pim-community/legacy-bridge/src/bridge/react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {RetailerApp} from '@akeneo-pim-enterprise/supplier-portal-retailer';

const mediator = require('oro/mediator');

class ProductFiles extends ReactController {
    private static container = document.createElement('div');

    reactElementToMount() {
        return <DependenciesProvider>
            <ThemeProvider theme={pimTheme}>
                <RetailerApp/>
            </ThemeProvider>
        </DependenciesProvider>;
    }

    routeGuardToUnmount() {
        return /^supplier_portal_retailer_product_files/;
    }

    renderRoute() {
        mediator.trigger('pim_menu:highlight:tab', {
            extension: 'pim-menu-connect',
            columnExtension: 'pim-menu-supplier-portal-column',
        });
        mediator.trigger('pim_menu:highlight:item', {extension: 'pim-menu-connect-supplier-portal-supplier-files-list'});

        return super.renderRoute();
    }

    getContainerRef(): Element {
        return ProductFiles.container;
    }
}

export = ProductFiles;
