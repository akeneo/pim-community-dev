import * as React from 'react';

import {Breadcrumb} from '../common/breadcrumb.component';
import {Page} from '../common/page.component';

const breadcrumb = (
    <Breadcrumb
        items={[
            {
                action: {
                    type: 'redirect',
                    route: 'oro_config_configuration_system',
                },
                label: 'pim_menu.item.configuration',
            },
        ]}
    />
);

const button = (
    <button type='button' className='AknButton AknButton--apply AknButtonList-item'>
        Create
    </button>
);

export const AppsList = () => (
    <Page breadcrumb={breadcrumb} actionButtons={[button]} title='Apps'>
        Hello world!
    </Page>
);
