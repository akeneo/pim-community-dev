/**
 * Allow to select on which tree to work and manage creation and deletion of trees
 * File: jstree.nested_switch.js
 *
/* Group: jstree nested_switch plugin */
(function ($) {
    var nested_switch_id = 'nested_switch_input';

    $.jstree.plugin('nested_switch', {
        __init: function () {
            this.get_container()
                // Create the tree toolbar and load trees in tree selector
                .bind('init.jstree', $.proxy(function () {
                    var settings = this._get_settings().nested_switch;
                    this.data.nested_switch.state    = settings.state;
                    this.data.nested_switch.label    = settings.label;
                    this.data.nested_switch.callback = settings.callback;
                    var _this = this;

                    var nested_switch_bar = $('<div>', {
                        id: 'nested_switch'
                    });

                    var nested_switch = $('<input>', {
                        type:    'checkbox',
                        id:      nested_switch_id,
                        'class': 'input-large jstree-tree-select',
                        checked: !!this.data.nested_switch.state
                    });

                    var switch_wrapper = $('<div>', {
                        'class': 'switch switch-small pull-right',
                        'attr' : {
                            'data-on-label':  'Yes',
                            'data-off-label': 'No',
                            'data-animated':  false
                        }
                    }).html(nested_switch);

                    var nested_switch_label = $('<label>', {
                        'for':    nested_switch_id,
                        'html':   this.data.nested_switch.label,
                        'class': 'control-label pull-left'
                    });

                    switch_wrapper.on('switch-change', function(e, data) {
                        // Execute callback with a timeout to give bootstrapSwitch time to change the switch
                        setTimeout(function() {
                            var callback = _this.data.nested_switch.callback;
                            if (callback) {
                                callback(data.value);
                            }
                        }, 25);
                    });

                    nested_switch_bar.html(nested_switch_label);
                    nested_switch_bar.append(switch_wrapper.bootstrapSwitch());
                    this.get_container_ul().after(nested_switch_bar);

                }, this))
                ;
        },
        defaults: {
            state:    true,
            label:    null,
            callback: null
        },
        _fn: {
            get_nested_switch: function () {
                return $('#' + nested_switch_id);
            }
        }
    });
    // include the nested_switchor plugin by default on available plugins list
    $.jstree.defaults.plugins.push('nested_switch');
})(jQuery);
