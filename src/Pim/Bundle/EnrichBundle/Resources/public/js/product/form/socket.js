'use strict';
/**
 * Socket extension
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'pim/form',
        'oro/mediator',
        'socketio'
    ],
    function (BaseForm, mediator, io) {
        return BaseForm.extend({
            configure: function () {
                var socket = io('pcd.dev:3000');

                // Each time the model is updated we emit an event
                mediator.on('pim_enrich:form:entity:update_state', function (e) {
                    socket.emit('update', this.getFormData());
                }.bind(this));

                // Each time we receive an update event we update the model
                socket.on('update', function (data) {
                    if (data.meta.id === this.getFormData().meta.id) {
                        this.setData(data);
                        this.getRoot().trigger('pim_enrich:form:entity:post_fetch', data);
                    }
                }.bind(this));

                return BaseForm.prototype.configure.apply(this, arguments);
            }
        });
    }
);
