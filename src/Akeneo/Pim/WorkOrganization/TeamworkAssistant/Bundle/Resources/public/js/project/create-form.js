'use strict';

/**
 * Form for Project
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
define([
  'jquery',
  'underscore',
  'oro/translator',
  'pim/form',
  'oro/loading-mask',
  'oro/messenger',
  'teamwork-assistant/saver/project',
  'datepicker',
  'pim/date-context',
  'pim/datagrid/state',
  'pim/user-context',
  'pim/formatter/date',
  'pim/fetcher-registry',
  'pim/i18n',
  'teamwork-assistant/templates/grid/create-project-modal-content',
  'teamwork-assistant/templates/field-error',
  'pim/template/form/creation/modal',
], function(
  $,
  _,
  __,
  BaseForm,
  LoadingMask,
  messenger,
  ProjectSaver,
  Datepicker,
  DateContext,
  DatagridState,
  UserContext,
  DateFormatter,
  FetcherRegistry,
  i18n,
  template,
  errorTemplate,
  templateModal
) {
  return BaseForm.extend({
    template: _.template(template),
    errorTemplate: _.template(errorTemplate),
    templateModal: _.template(templateModal),
    events: {
      'input .project-field': 'onInputField',
    },
    validationErrors: [],
    datetimepickerOptions: {
      format: DateContext.get('date').format,
      defaultFormat: DateContext.get('date').defaultFormat,
      language: DateContext.get('language'),
    },
    maxLengthLabel: 100,
    formType: 'create',

    /**
     * Set the backbone model for this view.
     *
     * @param {Backbone.Model} model
     */
    setModel: function(model) {
      this.model = model;
    },

    /**
     * Set the type of this form. Either "create" or "edit".
     *
     * @param {String} formType
     */
    setFormType: function(formType) {
      this.formType = formType;
    },

    /**
     * When an input occurs, it updates the model, validates fields and partial render form
     * when field values are not valid.
     */
    onInputField: function() {
      var model = this.getFormModel();

      this.updateModel(model);
      this.validateFields(model);
      this.partialRender();
    },

    /**
     * Update the model with data from the js form
     *
     * @param {Object} model
     */
    updateModel: function(model) {
      var dueDate = this.$('[name="project-due-date"]').val();

      if ('' !== dueDate) {
        dueDate = DateFormatter.format(dueDate, DateContext.get('date').format, 'yyyy-MM-dd');
      }

      model.set('label', this.$('[name="project-label"]').val());
      model.set('description', this.$('[name="project-description"]').val());
      model.set('due_date', dueDate);
    },

    /**
     * Validates fields from the model
     *
     * @param {Object} model
     */
    validateFields: function(model) {
      this.validationErrors = [];
      this.validateLabel(model.get('label'));
      this.validateDueDate(model.get('due_date'));
    },

    /**
     * Check if the model's label respects the max length.
     * Displays a message if too long and triggers an event to toggle the "Next" button of the modal.
     *
     * @param {string} label
     */
    validateLabel: function(label) {
      var isLabelTooLong = label.length > this.maxLengthLabel;

      if (isLabelTooLong) {
        this.validationErrors.push({
          field: 'label',
          message: __('teamwork_assistant.project.label_maxlength', {max: this.maxLengthLabel}),
        });
      }

      this.trigger('teamwork-assistant:edit-project:field-validated', 'label', !isLabelTooLong);
    },

    /**
     * Check if the model's due date isn't in the past.
     * Displays a message if in the past and triggers an event to toggle the "Next" button of the modal.
     *
     * @param {string} modelDueDate
     */
    validateDueDate: function(modelDueDate) {
      var isDueDateInPast = false;
      var isCreationMode = 'create' === this.formType;

      if (modelDueDate) {
        var today = new Date().setHours(0, 0, 0, 0);
        var dueDate = new Date(modelDueDate).setHours(0, 0, 0, 0);

        isDueDateInPast = today > dueDate;
      }

      var fieldIsInvalid = isCreationMode && isDueDateInPast;

      if (fieldIsInvalid) {
        this.validationErrors.push({
          field: 'dueDate',
          message: __('teamwork_assistant.project.due_date_past'),
        });
      }

      this.trigger('teamwork-assistant:edit-project:field-validated', 'due_date', !fieldIsInvalid);
    },

    /**
     * Save the form content by posting it to backend
     *
     * @return {Promise}
     */
    save: function() {
      this.validationErrors = [];
      var loadingMask = new LoadingMask();
      var project = _.defaults(this.getFormData(), {label: null});
      project.datagrid_view = DatagridState.get('product-grid', ['filters', 'columns']);
      project.locale = UserContext.get('catalogLocale');
      project.channel = UserContext.get('catalogScope');

      this.$el.empty().append(loadingMask.render().$el.show());

      return ProjectSaver.save(project)
        .done(
          function() {
            messenger.notify('success', __('teamwork_assistant.' + this.formType + '_project_modal.saved'));
          }.bind(this)
        )
        .fail(
          function(response) {
            if (_.isArray(response.responseJSON)) {
              this.validationErrors = response.responseJSON;
            } else {
              this.validationErrors.push(response.responseJSON);
            }

            this.validationErrors.forEach(error => {
              if (500 === error.status_code) {
                messenger.notify('error', error.status_text);
              }
            });

            this.render();
          }.bind(this)
        )
        .always(function() {
          loadingMask.remove();
        });
    },

    /**
     * Partial render validation part of the form.
     */
    partialRender: function() {
      $('.bootstrap-datetimepicker-widget:visible').hide();
      this.$('.label-errors').empty();
      this.$('.dueDate-errors').empty();

      if (0 < this.validationErrors.length) {
        _.each(
          this.validationErrors,
          function(error) {
            var selector = '.' + error.field + '-errors';

            this.$(selector).append(
              this.errorTemplate({
                message: error.message,
              })
            );
          }.bind(this)
        );
      }
    },

    /**
     * {@inheritdoc}
     */
    render: function() {
      $.when(this.getLocaleLabel(), this.getChannelLabel()).then(
        function(localeValue, channelValue) {
          var model = this.getFormModel();

          if ('edit' === this.formType) {
            var channel = model.get('channel');

            localeValue = model.get('locale').label;
            channelValue = i18n.getLabel(channel.labels, UserContext.get('catalogLocale'), channel.code);
          }

          this.$el.html(
            this.templateModal({
              fields: this.template({
                channelLabel: __('pim_enrich.entity.channel.uppercase_label'),
                channelValue: channelValue,
                localeLabel: __('teamwork_assistant.project.locale'),
                localeValue: localeValue,
                labelValue: model.get('label'),
                labelLabel: __('teamwork_assistant.project.label'),
                descriptionValue: model.get('description'),
                descriptionLabel: __('teamwork_assistant.project.description'),
                dueDateValue: DateFormatter.format(model.get('due_date'), 'yyyy-MM-dd', DateContext.get('date').format),
                dueDateLabel: __('teamwork_assistant.project.due_date'),
                errors: this.validationErrors,
                requiredLabel: __('teamwork_assistant.common.required'),
                warning: __('teamwork_assistant.common.warning'),
              }),
            })
          );

          this.initializeDatepicker();

          return this.renderExtensions();
        }.bind(this)
      );
    },

    /**
     * get user context locale
     *
     * @return {Promise}
     */
    getLocaleLabel: function() {
      var catalogLocale = UserContext.get('catalogLocale');

      return FetcherRegistry.getFetcher('locale')
        .fetch(catalogLocale)
        .then(function(locale) {
          return locale.label;
        });
    },

    /**
     * Get localized user context Channel label
     *
     * @return {Promise}
     */
    getChannelLabel: function() {
      var catalogChannel = UserContext.get('catalogScope');
      var catalogLocale = UserContext.get('catalogLocale');

      return FetcherRegistry.getFetcher('channel')
        .fetch(catalogChannel, {force_list_method: true})
        .then(function(channel) {
          return i18n.getLabel(channel.labels, catalogLocale, catalogChannel);
        });
    },

    /**
     * Initialize the datepicker component on the "Due date" input of the project.
     */
    initializeDatepicker: function() {
      Datepicker.init(this.$('.date-wrapper'), this.datetimepickerOptions)
        .on('show', function() {
          $('.bootstrap-datetimepicker-widget:visible').css('zIndex', 9999);
        })
        .on('changeDate', this.onInputField.bind(this));
    },
  });
});
