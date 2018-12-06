'use strict';

import * as React from 'react';
import * as ReactDOM from 'react-dom';
import PermissionCollectionEditor, {RightLevel, PermissionConfiguration} from 'akeneoreferenceentity/tools/component/permission';

const BaseView = require('pim/form');
const FetcherRegistry = require('pim/fetcher-registry');
const __ = require('oro/translator');

class Permission extends BaseView {
    className: 'AknTabContainer-content tabbable tabs-left permission'

    /**
     * {@inheritdoc}
     */
    constructor(options: { config: any }) {
        super(options);

        this.config = {...this.config, ...options.config};
    }

    configure() {
        this.trigger('tab:register', {
            code: this.config.tabCode ? this.config.tabCode : this.code,
            label: __(this.config.title)
        });

        return BaseView.prototype.configure.apply(this, arguments);
    }

    render () {
        FetcherRegistry.getFetcher('user-group')
            .fetchAll()
            .then((userGroups: any) => {
                ReactDOM.render(
                    <PermissionCollectionEditor
                        groups={userGroups}
                        entityName={this.config.entity}
                        value={this.getFormData().permissions}
                        prioritizedRightLevels={Object.keys(this.getFormData().permissions) as RightLevel[]}
                        onChange={(newValue: PermissionConfiguration) => {
                            this.permissionUpdated(newValue)
                        }}
                    />,
                    this.el
                  );
            });

        this.delegateEvents();

        return this;
    }

    /**
     * Update permission value
     *
     * @param {event} event
     */
    permissionUpdated(permissions: any) {
        this.setData({...this.getFormData(), permissions});
        this.render();
    }
}

export = Permission;
