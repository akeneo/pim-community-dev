/**
 * Allow to select on which tree to work and manage creation and deletion of trees
 * File: jstree.nested_switchor.js
 *
/* Group: jstree nested_switchor plugin */
(function ($) {
    var nested_switch_id = "nested_switch_id";

    $.jstree.plugin("nested_switch", {
        __init : function () {
            this.get_container()
                // Create the tree toolbar and load trees in tree selector
                .bind("init.jstree", $.proxy(function () {
                    var _this = this;

                    var nested_switch_bar = $('<div>', {
                        id: 'nested_switch'
                    });
                    // The class option can be used above as class is a reserved word
                    nested_switch_bar.addClass('nested_switch_bar');

                    var nested_switch = $('<input type="checkbox" id="nested_switch_input">', {
                        id: nested_switch_id,
                        class: 'input-large'
                    });
                    nested_switch.addClass('jstree-tree-select');
                    
                    nested_switch.bind('change', function() {
                        console.log('change');
                        // TODO : Refresh tree
                    });
                    
                    nested_switch_bar.html(nested_switch);
                    nested_switch_bar.append('Include sub-categories');
                    this.get_container_ul().after(nested_switch_bar);

                }, this))
                ;
        },
        _fn : {
            get_nested_switch : function () {
                return $("#" + nested_switch_id);
            }
        }
    });
    // include the nested_switchor plugin by default on available plugins list
    $.jstree.defaults.plugins.push("nested_switch");
})(jQuery);