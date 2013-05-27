var Oro = Oro || {};

/**
 * Loading mask widget
 *
 * @class   Oro.LoadingMask
 * @extends Backbone.View
 */
Oro.LoadingMask = Backbone.View.extend({

    /** @property {Boolean} */
    displayed: false,

    /** @property {Boolean} */
    liveUpdate: true,

    /** @property {String} */
    className: 'loading-mask',

    /** @property {String} */
    loadingHint: 'Loading...',

    /** @property */
    template:_.template(
        '<div id="loading-wrapper" class="loading-wrapper"></div>' +
        '<div id="loading-frame" class="loading-frame">' +
            '<div class="box well">' +
                '<div class="loading-content">' +
                    '<%= loadingHint %>' +
                '</div>' +
            '</div>' +
        '</div>'
    ),

    /**
     * Initialize
     *
     * @param {Object} options
     * @param {Boolean} [options.liveUpdate] Update position of loading animation on window scroll and resize
     */
    initialize: function(options) {
        options = options || {};

        if (_.has(options, 'liveUpdate')) {
            this.liveUpdate = options.liveUpdate;
        }

        if (this.liveUpdate) {
            var updateProxy = $.proxy(this.updatePos, this);
            $(window).resize(updateProxy).scroll(updateProxy);
        }
        Backbone.View.prototype.initialize.apply(this, arguments);
    },

    /**
     * Show loading mask
     *
     * @return {*}
     */
    show: function() {
        this.$el.show();
        this.displayed = true;
        this.resetPos().updatePos();
        return this;
    },

    /**
     * Update position of loading animation
     *
     * @return {*}
     * @protected
     */
    updatePos: function() {
        if (!this.displayed) {
            return this;
        }
        var $containerEl = this.$('.loading-wrapper');
        var $loadingEl = this.$('.loading-frame');

        var loadingHeight = $loadingEl.height();
        var containerTop = $containerEl.offset().top;
        var containerHeight = $containerEl.outerHeight();

        if (loadingHeight > containerHeight) {
            $containerEl.css('height', loadingHeight + 'px');
        }

        var windowHeight = $(window).outerHeight();
        var windowTop = $(window).scrollTop();
        var loadingTop = windowTop - containerTop + windowHeight / 2 - loadingHeight / 2;

        loadingTop = loadingTop < containerHeight - loadingHeight ? loadingTop : containerHeight - loadingHeight;
        loadingTop = loadingTop > 0 ? loadingTop : 0;
        loadingTop = Math.round(loadingTop);

        $loadingEl.css('top', loadingTop + 'px');
        return this;
    },

    /**
     * Update position of loading animation
     *
     * @return {*}
     * @protected
     */
    resetPos: function() {
        this.$('.loading-wrapper').css('height', '100%');
        return this;
    },

    /**
     * Hide loading mask
     *
     * @return {*}
     */
    hide: function() {
        this.$el.hide();
        this.displayed = false;
        this.resetPos();
        return this;
    },

    /**
     * Render loading mask
     *
     * @return {*}
     */
    render: function() {
        this.$el.empty();
        this.$el.append(this.template({
            loadingHint: this.loadingHint
        }));
        this.hide();
        return this;
    }
});
