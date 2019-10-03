import * as React from 'react';
import {Link} from 'react-router-dom';
import {Breadcrumb} from '../../common/Breadcrumb';
import {Page} from '../../common/Page';

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

export const ListApp = () => (
    <Page breadcrumb={breadcrumb} buttons={[button]} title='Apps'>
        ListApp
        <Link to='/apps/edit'>Edit</Link>
    </Page>
);
