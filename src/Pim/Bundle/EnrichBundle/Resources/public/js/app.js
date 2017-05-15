/**
 * Akeneo app
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'jquery',
        'underscore',
        'backbone',
        'pim/form',
        'oro/messenger',
        'pim/fetcher-registry',
        'pim/init',
        'pim/init-translator',
        'oro/init-layout',
        'pimuser/js/init-signin',
        'pim/page-title',
        'text!pim/template/app',
        'text!pim/template/header/flash'
    ],
    function (
        $,
        _,
        Backbone,
        BaseForm,
        messenger,
        FetcherRegistry,
        init,
        initTranslator,
        initLayout,
        initSignin,
        pageTitle,
        template,
        flashTemplate
    ) {
        return BaseForm.extend({
            tagName: 'div',
            className: 'app',
            template: _.template(template),
            flashTemplate: _.template(flashTemplate),

            /**
             * {@inheritdoc}
             */
            initialize: function () {
                initLayout();
                initSignin();

                return BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            configure: function () {
                return $.when(FetcherRegistry.initialize(), initTranslator.fetch())
                    .then(function () {
                        messenger.setup({
                            container: '.flash-messages-holder',
                            template: this.flashTemplate
                        });

                        init();

                        if (!Backbone.History.started) {
                            Backbone.history.start();
                        }

                        pageTitle.set('Akeneo PIM')

                        return BaseForm.prototype.configure.apply(this, arguments);
                    }.bind(this));
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                this.$el.html(this.template({}));

                return BaseForm.prototype.render.apply(this, arguments);
            }
        });
    });
