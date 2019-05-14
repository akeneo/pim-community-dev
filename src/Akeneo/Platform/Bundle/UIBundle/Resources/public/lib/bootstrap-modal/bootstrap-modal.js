/**
 * Bootstrap Modal wrapper for use with Backbone.
 *
 * Takes care of instantiation, manages multiple modals,
 * adds several options and removes the element from the DOM when closed
 *
 * @author Charles Davison <charlie@powmedia.co.uk>
 *
 * Events:
 * shown: Fired when the modal has finished animating in
 * hidden: Fired when the modal has finished animating out
 * cancel: The user dismissed the modal
 * ok: The user clicked OK
 */
(function($, _, Backbone) {
  var template = _.template('\
    <div class="AknFullPage">\
      <div class="AknFullPage-content<% if (typeof picture !== \'undefined\' || typeof illustrationClass !== \'undefined\') { %> AknFullPage-content--withIllustration<% } %>">\
        <div>\
          <% if (typeof picture !== \'undefined\') { %>\
            <img src="bundles/pimui/images/<%- picture %>" alt="<%- picture %>"/>\
          <% } else { %>\
            <div class="AknFullPage-image AknFullPage-illustration<% if (typeof illustrationClass !== \'undefined\') { %> AknFullPage-illustration--<%- illustrationClass %><% } %>"></div>\
          <% } %>\
        </div>\
        <div>\
          <div class="AknFullPage-titleContainer">\
            <% if (typeof subtitle !== \'undefined\') { %>\
              <div class="AknFullPage-subTitle"><%- subtitle %></div>\
            <% } %>\
            <div class="AknFullPage-title"><%- title %></div>\
            <% if (typeof innerDescription !== \'undefined\') { %>\
              <div class="AknFullPage-description">\
                <%- innerDescription %>\
              </div>\
            <% } %>\
          </div>\
          <div class="modal-body"><%= content %></div>\
          <div class="AknButtonList">\
            <% if (typeof cancelText !== undefined && cancelText !== \'\') { %>\
              <div class="AknButton AknButton--grey AknButtonList-item cancel"><%- cancelText %></div>\
            <% } %>\
            <div title="<%- okText %>" class="AknButton AknButtonList-item AknButton--apply<% if (typeof buttonClass !== \'undefined\') { %> <%- buttonClass %><% } %> ok"><%- okText %></div>\
          </div>\
        </div>\
      </div>\
    </div>\
    <div class="AknFullPage-cancel cancel"></div>\
  ');

  var Modal = Backbone.View.extend({

    className: 'modal',

    events: {
      'click .close': function(event) {
        event.preventDefault();

        this.trigger('cancel');

        if (this.options.content && this.options.content.trigger) {
          this.options.content.trigger('cancel', this);
        }
      },
      'click .cancel': function(event) {
        event.preventDefault();

        this.trigger('cancel');

        if (this.options.content && this.options.content.trigger) {
          this.options.content.trigger('cancel', this);
        }
      },
      'click .ok': function(event) {
        event.preventDefault();

        this.trigger('ok');

        if (this.options.content && this.options.content.trigger) {
          this.options.content.trigger('ok', this);
        }

        if (this.options.okCloses) {
          this.close();
        }
      }
    },

    /**
     * Creates an instance of a Bootstrap Modal
     *
     * @see http://twitter.github.com/bootstrap/javascript.html#modals
     *
     * @param {Object} options
     * @param {String|View} [options.content] Modal content. Default: none
     * @param {String} [options.title]        Title. Default: none
     * @param {String} [options.okText]       Text for the OK button. Default: 'OK'
     * @param {String} [options.cancelText]   Text for the cancel button. Default: 'Cancel'. If passed a falsey value, the button will be removed
     * @param {Boolean} [options.allowCancel  Whether the modal can be closed, other than by pressing OK. Default: true
     * @param {Boolean} [options.escape]      Whether the 'esc' key can dismiss the modal. Default: true, but false if options.cancellable is true
     * @param {Boolean} [options.animate]     Whether to animate in/out. Default: false
     * @param {Function} [options.template]   Compiled underscore template to override the default one
     * @param {string} [options.picture]      Image filename to load for a custom illustration.
     */
    initialize: function(options) {
      this.options = _.extend({
        title: null,
        okText: 'OK',
        focusOk: true,
        okCloses: true,
        cancelText: 'Cancel',
        allowCancel: true,
        escape: true,
        animate: false,
        buttons: true,
        template: template,
        picture: undefined
      }, options);
    },

    /**
     * Creates the DOM element
     *
     * @api private
     */
    render: function() {
      var $el = this.$el,
          options = this.options,
          content = options.content;

      //Create the modal container
      $el.html(options.template(options));

      var $content = this.$content = $el.find('.modal-body')

      //Insert the main content if it's a view
      if (content.$el) {
        content.render();
        $el.find('.modal-body').html(content.$el);
      }

      if (options.animate) $el.addClass('fade');

      this.isRendered = true;

      return this;
    },

    /**
     * Renders and shows the modal
     *
     * @param {Function} [cb]     Optional callback that runs only when OK is pressed.
     */
    open: function(cb) {
      if (!this.isRendered) this.render();
      this.delegateEvents();

      var self = this,
          $el = this.$el;

      //Create it
      $el.modal(_.extend({
        keyboard: this.options.allowCancel,
        backdrop: this.options.allowCancel ? true : 'static'
      }, this.options.modalOptions));

      //Focus OK button
      $el.one('shown', function() {
        if (self.options.focusOk) {
          $el.find('.btn.ok').focus();
        }

        if (self.options.content && self.options.content.trigger) {
          self.options.content.trigger('shown', self);
        }

        self.trigger('shown');
      });

      //Adjust the modal and backdrop z-index; for dealing with multiple modals
      var numModals = Modal.count,
          $backdrop = $('.modal-backdrop:eq('+numModals+')'),
          backdropIndex = parseInt($backdrop.css('z-index'), 10),
          elIndex = parseInt($backdrop.css('z-index'), 10) + 1;

      $backdrop.css('z-index', backdropIndex + numModals);
      this.$el.css('z-index', elIndex + numModals);

      if (this.options.allowCancel) {
        $backdrop.one('click', function() {
          if (self.options.content && self.options.content.trigger) {
            self.options.content.trigger('cancel', self);
          }

          self.trigger('cancel');
        });

        $(document).one('keyup.dismiss.modal', function (e) {
          e.which == 27 && self.trigger('cancel');

          if (self.options.content && self.options.content.trigger) {
            e.which == 27 && self.options.content.trigger('shown', self);
          }
        });
      }

      this.once('cancel', function() {
        self.close();
      });

      Modal.count++;

      //Run callback on OK if provided
      if (cb) {
        self.on('ok', cb);
      }

      return this;
    },

    /**
     * Closes the modal
     */
    close: function() {
      var self = this,
          $el = this.$el;

      //Check if the modal should stay open
      if (this._preventClose) {
        this._preventClose = false;
        return;
      }

      $el.one('hidden', function onHidden(e) {
        // Ignore events propagated from interior objects, like bootstrap tooltips
        if(e.target !== e.currentTarget){
          return $el.one('hidden', onHidden);
        }
        self.remove();

        if (self.options.content && self.options.content.trigger) {
          self.options.content.trigger('hidden', self);
        }

        self.trigger('hidden');
      });

      $el.modal('hide');

      Modal.count--;
    },

    /**
     * Stop the modal from closing.
     * Can be called from within a 'close' or 'ok' event listener.
     */
    preventClose: function() {
      this._preventClose = true;
    }
  }, {
    //STATICS

    //The number of modals on display
    count: 0
  });


  //EXPORTS
  //CommonJS
  if (typeof require == 'function' && typeof module !== 'undefined' && exports) {
    module.exports = Modal;
  }

  //AMD / RequireJS
  if (typeof define === 'function' && define.amd) {
    return define(function() {
      Backbone.BootstrapModal = Modal;
    })
  }

  //Regular; add to Backbone.Bootstrap.Modal
  else {
    Backbone.BootstrapModal = Modal;
  }

})(jQuery, _, Backbone);
