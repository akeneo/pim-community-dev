/**
 * Allow to select on which tree to work and manage creation and deletion of trees
 * File: jstree.nested_switch.js
 *
/* Group: jstree nested_switch plugin */
(function ($) {
    var nested_switch_id = 'nested_switch_input';

    $.jstree.plugin('nested_switch', {
        __init : function () {
            this.get_container()
                // Create the tree toolbar and load trees in tree selector
                .bind('init.jstree', $.proxy(function () {
                    var _this = this;

                    var nested_switch_bar = $('<div>', {
                        id:      'nested_switch',
                        'class': 'nested_switch_bar'
                    });

                    var nested_switch = $('<input>', {
                        type:    'checkbox',
                        id:      nested_switch_id,
                        'class': 'input-large jstree-tree-select'
                    });

                    var nested_switch_label = $('<label>', {
                        'for':  nested_switch_id,
                        'html': 'Include sub-categories' // TODO: translate
                    });

                    nested_switch.bind('change', function() {
                        console.log('change');
                        // TODO : Refresh tree
                    });

                    nested_switch_bar.html(nested_switch_label);
                    nested_switch_bar.append(nested_switch);
                    this.get_container_ul().after(nested_switch_bar);

                }, this))
                ;
        },
        _fn : {
            get_nested_switch : function () {
                return $('#' + nested_switch_id);
            }
        }
    });
    // include the nested_switchor plugin by default on available plugins list
    $.jstree.defaults.plugins.push('nested_switch');
})(jQuery);
