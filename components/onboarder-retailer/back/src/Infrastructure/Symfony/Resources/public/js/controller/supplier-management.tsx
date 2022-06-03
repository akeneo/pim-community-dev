import React from 'react';
import {ReactController} from '@akeneo-pim-community/legacy-bridge/src/bridge/react';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {SupplierApp} from '@akeneo-pim-enterprise/onboarder';

const mediator = require('oro/mediator');

class SupplierManagement extends ReactController {
    private static container = document.createElement('div');

    reactElementToMount() {
        return <DependenciesProvider>
            <ThemeProvider theme={pimTheme}>
                <SupplierApp/>
            </ThemeProvider>
        </DependenciesProvider>;
    }

    routeGuardToUnmount() {
        return /^onboarder_serenity_supplier_/;
    }

    renderRoute() {
        mediator.trigger('pim_menu:highlight:tab', {
            extension: 'pim-menu-connect',
            columnExtension: 'pim-menu-onboarder-column',
        });
        mediator.trigger('pim_menu:highlight:item', {extension: 'pim-menu-connect-onboarder-supplier-list'});

        return super.renderRoute();
    }

    getContainerRef(): Element {
        return SupplierManagement.container;
    }
}

export = SupplierManagement;
