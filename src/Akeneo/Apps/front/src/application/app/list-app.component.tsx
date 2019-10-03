import * as React from 'react';
import {HashRouter as Router, Link, Route, Switch} from 'react-router-dom';
import {Breadcrumb} from '../common/breadcrumb.component';
import {Page} from '../common/page.component';
import {EditApp} from './edit-app.component';

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
    <Page breadcrumb={breadcrumb} actionButtons={[button]} title='Apps'>
        <Router>
            <Link to='/apps/edit'>Edit</Link>
            <Switch>
                <Route path='/apps/edit'>
                    <EditApp />
                </Route>
                <Route path='/apps'>Hello world!</Route>
            </Switch>
        </Router>
    </Page>
);
