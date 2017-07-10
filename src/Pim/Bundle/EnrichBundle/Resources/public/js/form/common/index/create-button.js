

/**
 * Create button
 *
 * @author    Alban Alnot <alban.alnot@consertotech.pro>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
import $ from 'jquery';
import _ from 'underscore';
import __ from 'oro/translator';
import BaseForm from 'pim/form';
import template from 'pim/template/form/index/create-button';
import Routing from 'routing';
import DialogForm from 'pim/dialogform';
export default BaseForm.extend({
    template: _.template(template),
    dialog: null,

            /**
             * {@inheritdoc}
             */
    initialize: function (config) {
        this.config = config.config;

        BaseForm.prototype.initialize.apply(this, arguments);
    },

            /**
             * {@inheritdoc}
             */
    render: function () {
        this.$el.html(this.template({
            title: __(this.config.title),
            iconName: this.config.iconName,
            url: Routing.generate(this.config.url)
        }));

        this.dialog = new DialogForm('#create-button-extension');

        return this;
    }
});

