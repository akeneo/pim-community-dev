

/**
 * Confirm button extension
 *
 * @author    Alban Alnot <alban.alnot@consertotech.pro>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
import _ from 'underscore'
import __ from 'oro/translator'
import BaseForm from 'pim/form'
import Routing from 'routing'
import template from 'pim/template/form/index/confirm-button'
export default BaseForm.extend({
    template: _.template(template),

            /**
             * {@inheritdoc}
             */
    initialize: function (config) {
        this.config = config.config || {}

        BaseForm.prototype.initialize.apply(this, arguments)
    },

            /**
             * {@inheritdoc}
             */
    render: function () {
        this.$el.html(this.template({
            buttonClass: this.config.buttonClass,
            buttonLabel: __(this.config.buttonLabel),
            title: __(this.config.title),
            message: __(this.config.message),
            url: Routing.generate(this.config.url),
            redirectUrl: Routing.generate(this.config.redirectUrl),
            errorMessage: __(this.config.errorMessage),
            successMessage: __(this.config.successMessage)
        }))

        this.renderExtensions()

        return this
    }
})

