'use strict';
/**
 * Copy extension override able to copy from product working copy
 *
 * @author Yohan Blain <yohan.blain@akeneo.com>
 */
define([
  'underscore',
  'oro/translator',
  'backbone',
  'pim/form/common/attributes/copy',
  'pim/fetcher-registry',
  'pim/user-context',
  'pim/feature-flags',
], function(_, __, Backbone, Copy, FetcherRegistry, UserContext, FeatureFlags) {
  if (!FeatureFlags.isEnabled('proposal')) {
    return Copy;
  }

  /**
   * Internal function that returns an union of current sources without drafts and the given drafts
   *
   * @param {Array} sources
   * @param {Array} drafts
   *
   * @return {Array}
   */
  var mergeSourcesAndDrafts = function(sources, drafts) {
    drafts = _.reject(drafts, function(draft) {
      return draft.author === UserContext.get('username');
    });

    return _.union(
      _.reject(sources, function(source) {
        return 'draft' === source.type;
      }),
      _.map(_.pluck(drafts, 'author'), function(author) {
        return {
          code: 'draft_of_' + author,
          label: __('pimee_enrich.entity.product.module.copy.draft_of', {author: author}),
          type: 'draft',
          author: author,
        };
      })
    );
  };

  return Copy.extend({
    sources: [],
    currentSource: null,
    otherDrafts: [],
    otherDraftsPromise: null,

    /**
     * {@inheritdoc}
     */
    initialize: function() {
      this.sources = [
        {
          code: 'working_copy',
          label: __('pimee_enrich.entity.product.module.copy.working_copy'),
          type: 'working_copy',
          author: null,
        },
        {
          code: 'my_draft',
          label: __('pimee_enrich.entity.product.module.copy.draft'),
          type: 'my_draft',
          author: null,
        },
      ];

      this.currentSource = _.first(this.sources);

      Copy.prototype.initialize.apply(this, arguments);
    },

    /**
     * @inheritdoc
     */
    configure: function() {
      this.otherDraftsPromise = null;

      this.listenTo(this.getRoot(), 'pim_enrich:form:draft:show_working_copy', this.startCopyingWorkingCopy);
      this.listenTo(this.getRoot(), 'pim_enrich:form:proposal:post_remove:success', this.invalidDraftPromise);

      this.onExtensions('pim_enrich:form:source_switcher:render:before', this.ensureSwitcherContext);
      this.onExtensions('pim_enrich:form:source_switcher:source_change', this.changeCurrentSource);

      return Copy.prototype.configure.apply(this, arguments);
    },

    /**
     * @inheritdoc
     */
    render: function() {
      if (this.copying && !this.otherDraftsPromise) {
        const fetcherId =
          this.options && this.options.config && this.options.config.fetcher
            ? this.options.config.fetcher
            : 'product_draft';
        this.otherDraftsPromise = FetcherRegistry.getFetcher(fetcherId)
          .fetchAllById(this.getFormData().meta.id)
          .then(
            function(drafts) {
              this.otherDrafts = drafts;
              this.sources = mergeSourcesAndDrafts(this.sources, drafts);
            }.bind(this)
          );
      }

      if (this.copying) {
        this.otherDraftsPromise.then(
          function() {
            return Copy.prototype.render.apply(this, arguments);
          }.bind(this)
        );

        return this;
      }

      return Copy.prototype.render.apply(this, arguments);
    },

    /**
     * @inheritdoc
     */
    getSourceData: function() {
      switch (this.currentSource.type) {
        case 'working_copy':
          return _.result(this.getFormData().meta.working_copy, 'values', {});
        case 'draft':
          return _.findWhere(this.otherDrafts, {author: this.currentSource.author}).changes.values;
        default:
          return Copy.prototype.getSourceData.apply(this, arguments);
      }
    },

    /**
     * @inheritdoc
     */
    canBeCopied: function(field) {
      var params = {
        field: field,
        canBeCopied: Copy.prototype.canBeCopied.apply(this, arguments),
        locale: this.locale,
        scope: this.scope,
      };

      // Assets are not in the PEF (they are in a dedicated tab) and therefore should not be copied.
      if (params.field.fieldType === 'pim-asset-collection-field') {
        return false;
      }

      switch (this.currentSource.type) {
        case 'working_copy':
          this.getRoot().trigger('pim_enrich:form:field:can_be_copied', params);
          break;
        case 'draft':
          params.canBeCopied = field.attribute.code in this.getSourceData();
          break;
      }

      return params.canBeCopied;
    },

    /**
     * Keep any source switcher up-to-date for its rendering
     *
     * @param {Object} context
     */
    ensureSwitcherContext: function(context) {
      // If the user owns the product, my_draft is not a valid source
      if (null === this.getFormData().meta.draft_status) {
        context.sources = _.reject(this.sources, function(source) {
          return 'my_draft' === source.code;
        });
      } else {
        context.sources = this.sources;
      }

      context.currentSource = this.currentSource;
    },

    /**
     * Update the current source and re-render the extension
     *
     * @param {string} code
     */
    changeCurrentSource: function(code) {
      this.currentSource = _.findWhere(this.sources, {code: code});
      this.triggerContextChange();
    },

    /**
     * Set the current source to "working copy" and enter in copy mode
     */
    startCopyingWorkingCopy: function() {
      this.currentSource = _.findWhere(this.sources, {code: 'working_copy'});
      this.startCopying();
    },

    /**
     * Invalid the cached promises on user drafts
     */
    invalidDraftPromise: function() {
      this.otherDraftsPromise = null;
      this.changeCurrentSource('working_copy');
      this.stopCopying();
    },
  });
});
