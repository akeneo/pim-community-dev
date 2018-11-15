'use strict';
/**
 * Redirect button
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define([
        'underscore',
        'oro/translator',
        'pim/form',
        'pim/fetcher-registry',
        'pim/common/property',
        'pimee/template/form/tab/permission'
    ],
    function (
        _,
        __,
        BaseForm,
        FetcherRegistry,
        propertyAccessor,
        template
    ) {
        return BaseForm.extend({
            className: 'AknTabContainer-content tabbable tabs-left permission',
            template: _.template(template),
            originalPermissions: {},
            events: {
                'change select': 'permissionUpdated'
            },

            /**
             * @param {Object} meta
             */
            initialize: function (meta) {
                this.config = _.extend({}, meta.config);
            },

            /**
             * {@inheritdoc}
             */
            configure: function () {

                this.trigger('tab:register', {
                    code: this.config.tabCode ? this.config.tabCode : this.code,
                    label: __(this.config.title)
                });

                this.listenTo(
                    this.getRoot(),
                    'pim_enrich:form:entity:post_fetch',
                    this.updateOriginalPermissions.bind(this)
                );

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                FetcherRegistry.getFetcher('user-group')
                    .fetchAll()
                    .then(function (userGroups) {
                        this.$el.html(this.template({
                            userGroups: userGroups,
                            __: __,
                            entity: this.config.entity,
                            permissions: this.getFormData().permissions,
                            changes: this.computeChanges(this.originalPermissions, this.getFormData().permissions),
                            readOnly: this.config.readOnly
                        }));
                        this.$('.select2').select2();
                    }.bind(this));

                this.delegateEvents();

                return this;
            },

            /**
             * Update permission value
             *
             * @param {event} event
             */
            permissionUpdated: function (event) {
                var permission = event.currentTarget.dataset.permission;
                var value = _.map(_.filter(event.currentTarget.options, {selected: true}), function (option) {
                    return option.value;
                });

                var data = this.getFormData();

                data.permissions = this.updateRelativePermissions(
                    data.permissions,
                    _.keys(this.originalPermissions),
                    permission,
                    value
                );

                this.setData(data);
                this.render();
            },

            /**
             * Save original permissions
             */
            updateOriginalPermissions: function () {
                this.originalPermissions = this.getFormData().permissions;
            },

            /**
             * Update permission for higher or lower permissions (for example, if you add edit right you want
             * to add view rights and if you remove view right, you want the edit right to be removed too)
             *
             * @param {object} modelPermissions         The current permissions (before modification)
             * @param {object} permissionsOrder         Is edit right more important thant execute right ?
             * @param {string} permissionActionToUpdate Which action do we want to update ? Edit, execute, view ?
             * @param {array}  newValue                 The new value for this permission, represented by an array of
             *                                          user group
             *
             * @return {object} The updated permissions
             */
            updateRelativePermissions: function (
                modelPermissions,
                permissionsOrder,
                permissionActionToUpdate,
                newValue
            ) {
                var result = _.extend({}, modelPermissions);
                if (modelPermissions[permissionActionToUpdate].length > newValue.length) {
                    var removed = _.difference(
                        modelPermissions[permissionActionToUpdate],
                        newValue
                    );

                    result = _.each(result, function (value, key) {
                        result[key] = permissionsOrder.indexOf(key) >= permissionsOrder
                                .indexOf(permissionActionToUpdate) ?
                            _.difference(value, removed) :
                            value;
                    });
                } else {
                    var added = _.difference(newValue, modelPermissions[permissionActionToUpdate]);

                    result = _.each(result, function (value, key) {
                        result[key] = permissionsOrder
                                .indexOf(key) <= permissionsOrder.indexOf(permissionActionToUpdate) ?
                            _.union(value, added) :
                            value;
                    });
                }

                return result;
            },

            /**
             * Compute the changes on permissions
             *
             * @param {object} originalPermissions
             * @param {object} newPermissions
             *
             * @return {object}
             */
            computeChanges: function (originalPermissions, newPermissions) {
                var changes = {added: [], removed: []};

                _.each(originalPermissions, function (value, key) {
                    changes.added = _.union(
                        changes.added,
                        _.map(_.difference(newPermissions[key], value), function (value) {
                            return {
                                group: value,
                                permission: key
                            };
                        })
                    );
                    changes.removed = _.union(
                        changes.removed,
                        _.map(_.difference(value, newPermissions[key]), function (value) {
                            return {
                                group: value,
                                permission: key
                            };
                        })
                    );
                });

                return changes;
            }
        });
    }
);
