'use strict';

/**
 * Override to display a flash message if a user is leaving the filters scope of a project.
 *
 * @author    Adrien Petremann <adrien.petremann@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'underscore',
        'oro/translator',
        'oro/messenger',
        'pim/grid/view-selector/current',
        'text!teamwork-assistant/templates/grid/view-selector/current'
    ],
    function (
        _,
        __,
        messenger,
        BaseCurrent,
        template
    ) {
        return BaseCurrent.extend({
            template: _.template(template),
            notified: false,

            /**
             * {@inheritdoc}
             */
            onDatagridStateChange: function () {
                // If view type switcher is on "project" and there is no project to display,
                // then we don't react to datagrid change
                if ('project' === this.getRoot().currentViewType && this.getRoot().hasNoProject) {
                    return;
                }

                BaseCurrent.prototype.onDatagridStateChange.apply(this, arguments);

                if ('project' !== this.datagridView.type) {
                    return;
                }

                if (this.notified && !this.dirtyFilters) {
                    this.notified = false;
                } else if (!this.notified && this.dirtyFilters) {
                    messenger.notificationFlashMessage('warning', __('teamwork_assistant.project.leaving_scope'));
                    this.notified = true;
                }
            },

            /**
             * {@inheritdoc}
             *
             * Override to omit "items per page" and "current page" filters
             */
            areFiltersModified: function (initialViewFilters, datagridStateFilters) {
                if ('project' !== this.datagridView.type) {
                    return BaseCurrent.prototype.areFiltersModified.apply(this, arguments);
                }

                // Regex to remove items per page (p) and current page (i) of the filters url
                var regex = /(i=\d+&p=\d+&)/;

                return initialViewFilters.replace(regex, '') !== datagridStateFilters.replace(regex, '');
            }
        });
    }
);
