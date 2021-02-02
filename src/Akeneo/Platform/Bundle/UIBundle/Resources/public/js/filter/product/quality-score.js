'use strict';

define([
    'underscore',
    'oro/translator',
    'pim/filter/filter',
    'pim/fetcher-registry',
    'pim/user-context',
    'oro/mediator',
    'pim/template/filter/product/quality-score-filter',
    'pim/template/filter/product/quality-score',
    'pim/template/filter/product/quality-score-operator',
    'jquery.select2',
], function (
  _,
  __,
  BaseFilter,
  FetcherRegistry,
  UserContext,
  mediator,
  filterTemplate,
  template,
  templateOperator
) {
    return BaseFilter.extend({
        className: '',
        shortname: 'quality-score',
        filterTemplate: _.template(filterTemplate),
        template: _.template(template),
        templateOperator: _.template(templateOperator),
        events: {
            'change [name="filter-value"]': 'updateState',
            'change [name="filter-operator"]': 'updateState',
        },

        /**
         * {@inheritdoc}
         */
        initialize: function (config) {
            this.config = config.config;

            this.selectScoreOptions = {};
            this.selectOperatorOptions = {
                minimumResultsForSearch: -1,
            };

            return BaseFilter.prototype.initialize.apply(this, arguments);
        },


        /**
         * {@inheritdoc}
         */
        configure: function () {
            this.listenTo(
              this.getRoot(),
              'pim_enrich:form:entity:pre_update',
              function (data) {
                  _.defaults(data, {field: this.getCode(), operator: this.getOperator()});
              }.bind(this)
            );

            return BaseFilter.prototype.configure.apply(this, arguments);
        },

        /**
         * {@inheritdoc}
         */
        isEmpty: function () {
            return _.isEmpty(this.getValue());
        },

        /**
         * {@inheritdoc}
         */
        renderInput: function () {
            return this.template({
                __: __,
                field: this.getField(),
                isEditable: this.isEditable(),
                value: this.getValue(),
                scoreChoices: ['a', 'b', 'c', 'd', 'e'],
                shortname: this.shortname,
            });
        },

        renderOperator: function () {
            return this.templateOperator({
                __: __,
                field: this.getField(),
                isEditable: this.isEditable(),
                operator: this.getOperator(),
                operatorChoices: this.config.operators,
                shortname: this.shortname,
            });
        },

        /**
         * {@inheritdoc}
         */
        render: function () {
            var promises = [];
            this.elements = {};
            this.setEditable(true);

            mediator.trigger('pim_enrich:form:filter:extension:add', {filter: this, promises: promises});

            $.when
              .apply($, promises)
              .then(this.getTemplateContext.bind(this))
              .then(
                function (templateContext) {
                    this.el.dataset.name = this.getField();
                    this.el.dataset.type = this.getType();

                    this.$el.html(this.filterTemplate(templateContext));
                    this.$('.remove').on('click', this.removeFilter.bind(this));
                    this.$('.filter-input').replaceWith(this.renderInput(templateContext));
                    this.$('.filter-operator').replaceWith(this.renderOperator(templateContext));

                    this.renderElements();
                    this.postRender(templateContext);
                    this.delegateEvents();
                }.bind(this)
              );

            return this;
        },


        /**
         * {@inheritdoc}
         */
        getTemplateContext: function () {
            return $.Deferred()
              .resolve({
                  label: __('pim_enrich.export.product.filter.' + this.shortname + '.title'),
                  label_operator: __('pim_enrich.export.product.filter.' + this.shortname + '.operator_choice_title'),
                  removable: false,
                  editable: this.isEditable(),
              })
              .promise();
        },

        /**
         * {@inheritdoc}
         */
        postRender: function () {
            this.$('[name="filter-operator"]').select2(this.selectOperatorOptions);
            this.$('[name="filter-value"]').select2(this.selectScoreOptions);
        },

        /**
         * {@inheritdoc}
         */
        updateState: function () {
            const value = this.$('[name="filter-value"]').val();
            const operator = this.$('[name="filter-operator"]').val();
            const cleanedValues = _.reject(value, function (val) {
                return '' === val;
            });

            this.setData({
                field: this.getField(),
                operator: operator,
                value: cleanedValues,
                context: {
                    locales: this.getParentForm().getFilters().structure.locales,
                },
            });
        },
    });
});
