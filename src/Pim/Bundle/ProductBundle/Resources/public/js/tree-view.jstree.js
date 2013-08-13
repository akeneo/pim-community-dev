var Pim = Pim || {};
Pim.tree = Pim.tree || {};

Pim.tree.view = function(elementId) {
    var $el = $('#'+elementId);
    if (!$el || !$el.length || !_.isObject($el)) {
        throw new Error('Unable to instantiate tree on this element');
    }
    var assetsPath = $el.attr('data-assets-path'),
    listTreeUrl = $el.attr('data-list-tree-url'),
    childrenUrl = $el.attr('data-children-url'),
    unclassifiedNodeTitle = $el.attr('data-unclassified-title'),
    self = this;

    this.config = {
        'core': {
            'animation': 200
        },
        'plugins': [
             'tree_selector',
             'themes',
             'json_data',
             'ui',
             'crrm',
             'types'
        ],
        'tree_selector': {
            'ajax': {
                'url': listTreeUrl
            },
            'auto_open_root': true,
            'node_label_field': 'title'
        },
        'themes': {
            'dots': true,
            'icons': true,
            'themes': 'bap',
            'url': assetsPath + '/css/style.css'
        },
        'json_data': {
            'ajax': {
                'url': childrenUrl,
                'data': function (node) {
                    // the result is fed to the AJAX request `data` option
                    var id = (node && node != -1) ? node.attr('id').replace('node_','') : -1;
                    return {
                        'id': id,
                        'with_products_count': 'true'
                    };
                }
            }
        },
        'types': {
            'max_depth': -2,
            'max_children': -2,
            'valid_children': [ 'folder' ],
            'types': {
                'default': {
                    'valid_children': 'folder'
                }
            }
        },
        'ui': {
            'select_limit': 1,
            'select_multiple_modifier': false
        }
    };

    this.init = function() {
        $el.jstree(self.config)
        .on('trees_loaded.jstree', function (event, tree_select_id) {
            if (event.namespace == 'jstree') {
                $('#'+tree_select_id).select2({ width: '100%' });
            }
        })
        .on('after_tree_loaded.jstree', function (root_node_id) {
            $el.jstree('create', root_node_id, 'last', {
                'attr': { 'class': 'jstree-unclassified', 'id': 'node_0' },
                'data': { 'title': unclassifiedNodeTitle }
            }, false, true);
        })
        .on('select_node.jstree', function (event, data) {
            var nodeId = $.jstree._focused().get_selected().attr('id').replace('node_', '');
            var treeId = $('#tree li').first().attr('id').replace('node_', '');
            var datagrid = Oro.Registry.getElement('datagrid', 'products');
            datagrid.collection.url = datagrid.collection.url + '&treeId=' + treeId + '&categoryId=' + nodeId;
            $('.grid-toolbar .actions-panel .action.btn .icon-refresh').click();
        });
    };

    this.init();
};
