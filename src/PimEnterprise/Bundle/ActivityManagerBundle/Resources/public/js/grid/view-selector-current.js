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
        'oro/translator',
        'oro/messenger',
        'pim/grid/view-selector/current',
        'text!activity-manager/templates/grid/view-selector/current'
    ],
    function (
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
                BaseCurrent.prototype.onDatagridStateChange.apply(this, arguments);

                if ('project' !== this.datagridView.type) {
                    return;
                }

                if (this.notified && !this.dirtyFilters) {
                    this.notified = false;
                } else if (!this.notified && this.dirtyFilters) {
                    messenger.notificationFlashMessage('warning', __('activity_manager.project.leaving_scope'));
                    this.notified = true;
                }
            }
        });
    }
);
