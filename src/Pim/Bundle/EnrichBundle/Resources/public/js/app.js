/**
 * Akeneo app
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
import $ from 'jquery'
import _ from 'underscore'
import Backbone from 'backbone'
import BaseForm from 'pim/form'
import messenger from 'oro/messenger'
import FetcherRegistry from 'pim/fetcher-registry'
import init from 'pim/init'
import initTranslator from 'pim/init-translator'
import initLayout from 'oro/init-layout'
import initSignin from 'pimuser/js/init-signin'
import pageTitle from 'pim/page-title'
import template from 'pim/template/app'
import flashTemplate from 'pim/template/common/flash'
export default BaseForm.extend({
    tagName: 'div',
    className: 'app',
    template: _.template(template),
    flashTemplate: _.template(flashTemplate),

            /**
             * {@inheritdoc}
             */
    initialize: function () {
        initLayout()
        initSignin()

        return BaseForm.prototype.initialize.apply(this, arguments)
    },

            /**
             * {@inheritdoc}
             */
    configure: function () {
        return $.when(FetcherRegistry.initialize(), initTranslator.fetch())
                    .then(function () {
                        messenger.showQueuedMessages()

                        init()

                        pageTitle.set('Akeneo PIM')

                        return BaseForm.prototype.configure.apply(this, arguments)
                    }.bind(this))
    },

            /**
             * {@inheritdoc}
             */
    render: function () {
        this.$el.html(this.template({}))

        if (!Backbone.History.started) {
            Backbone.history.start()
        }

        return BaseForm.prototype.render.apply(this, arguments)
    }
})

