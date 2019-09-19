/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
'use strict';

define([
  'jquery',
  'underscore',
  'pim/form/common/fields/field',
  'oro/translator',
  'pim/i18n',
  'pim/initselect2',
  'pim/user-context',
  'pim/template/form/common/fields/simple-select-async',
  'routing'
],
function (
  $,
  _,
  BaseField,
  __,
  i18n,
  initSelect2,
  UserContext,
  template,
  Routing
) {
  return BaseField.extend({
    template: _.template(template),
    choiceUrl: null,
    resultsPerPage: 20,
    allowClear: false,

    /**
     * {@inheritdoc}
     */
    initialize() {
      this.choiceUrl = null;
      this.choiceVerb = 'GET';

      BaseField.prototype.initialize.apply(this, arguments);

      if (undefined !== this.config.choiceRoute) {
        this.setChoiceUrl(Routing.generate(this.config.choiceRoute));
      }
      if (undefined !== this.config.choiceVerb) {
        this.choiceVerb = this.config.choiceVerb;
      }
    },

    /**
     * Sets the URL that will be used by select2 to fetch choices from the backend.
     *
     * @param {String} choiceUrl
     */
    setChoiceUrl(choiceUrl) {
      this.choiceUrl = choiceUrl;
    },

    /**
     * {@inheritdoc}
     */
    renderInput: function(templateContext) {
      return this.template(
        _.extend(templateContext, {
          value: this.getModelValue(),
        })
      );
    },

    /**
     * {@inheritdoc}
     */
    postRender(templateContext) {
      const $select2 = this.$('.select2');

      initSelect2.init($select2, this.getSelect2Options(templateContext));

      const onChange = () => {
        this.errors = [];
        this.updateModel(this.getFieldValue($select2));
        this.getRoot().render();
      }

      $select2.on('change', onChange);
    },

    /**
     * Returns the options for Select2 library
     *
     * @returns {Object}
     */
    getSelect2Options() {
      return {
        ajax: {
          url: this.choiceUrl,
          cache: true,
          data: this.select2Data.bind(this),
          results: this.select2Results.bind(this),
          type: this.choiceVerb,
        },
        initSelection: this.select2InitSelection.bind(this),
        placeholder: undefined !== this.config.placeholder ? __(this.config.placeholder) : ' ',
        dropdownCssClass: '',
        allowClear: this.allowClear,
      };
    },

    /**
     * Formatting callback for select2 choices.
     *
     * @param {String} term
     * @param {Number} page
     *
     * @returns {Object}
     */
    select2Data(term, page) {
      return {
        search: term,
        options: {
          limit: this.resultsPerPage,
          page: page,
          catalogLocale: UserContext.get('catalogLocale'),
        },
      };
    },

    /**
     * Select2 customization for pagination.
     *
     * @param response
     *
     * @returns {Object}
     */
    select2Results(response) {
      const nbResults = response.results ? Object.keys(response.results).length : Object.keys(response).length;
      const more = this.resultsPerPage === nbResults;

      // The result is already formatted for select2
      if (response.results) {
        response.more = more;

        return response;
      }

      // The result is an array
      if (response.isArray) {
        return {
          more: more,
          results: response.map(item => this.convertBackendItem(item)),
        };
      }

      // The result is an object
      return {
        more: more,
        results: Object.values(response).map(item => this.convertBackendItem(item)),
      };
    },

    /**
     * Select2 callback to fetch the initial value and display it properly.
     *
     * @param {Element} element
     * @param {Function} callback
     */
    select2InitSelection(element, callback) {
      const id = $(element).val();
      if ('' !== id) {
        $.ajax({
          url: this.choiceUrl,
          data: {options: {identifiers: [id]}},
          type: this.choiceVerb,
        }).then(response => {
          let selected = _.findWhere(response, {code: id});

          if (!selected) {
            selected = _.findWhere(response.results, {id: id});
          } else {
            selected = this.convertBackendItem(selected);
          }
          callback(selected);
        });
      }
    },

    /**
     * Converts the item returned from the backend to fit select2 needs.
     *
     * @param {Object} item
     *
     * @returns {Object}
     */
    convertBackendItem(item) {
      if (undefined !== item.label) {
        return {
          id: item.code,
          text: item.label,
        };
      }

      return {
        id: item.code,
        text: i18n.getLabel(item.labels, UserContext.get('catalogLocale'), item.code),
      };
    },

    /**
     * @param {Element} field
     *
     * @returns {String}
     */
    getFieldValue(field) {
      return $(field).val();
    },
  });
});
