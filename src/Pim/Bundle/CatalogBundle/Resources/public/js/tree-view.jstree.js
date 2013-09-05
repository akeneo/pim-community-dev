var Pim = Pim || {};
Pim.tree = Pim.tree || {};

Pim.tree.view = function(elementId) {
    var $el = $('#'+elementId);
    if (!$el || !$el.length || !_.isObject($el)) {
        throw new Error('Unable to instantiate tree on this element');
    }
    var self   = this,
    assetsPath = $el.attr('data-assets-path'),
    dataLocale = $el.attr('data-datalocale');

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
                'url': Routing.generate('pim_catalog_categorytree_listtree', { '_format': 'json', 'dataLocale': dataLocale })
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
                'url': Routing.generate('pim_catalog_categorytree_children', { '_format': 'json', 'dataLocale': dataLocale }),
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

    function updateGrid(treeId, nodeId) {
        var treePattern = /(&treeId=(\d+))/,
        nodePattern = /(&categoryId=(\d+))/;

        var datagrid = Oro.Registry.getElement('datagrid', 'products');
        var url = datagrid.collection.url;

        var treeString = nodeId === '' ? '' : '&treeId=' + treeId;
        var nodeString = nodeId === '' ? '' : '&categoryId=' + nodeId;

        if (url.match(treePattern)) {
            url = url.replace(treePattern, treeString);
        } else {
            url += treeString;
        }

        if (url.match(nodePattern)) {
            url = url.replace(nodePattern, nodeString);
        } else {
            url += nodeString;
        }

        if (datagrid.collection.url !== url) {
            datagrid.collection.url = url;
            $('.grid-toolbar .actions-panel .action.btn .icon-refresh').click();
        }
    }

    this.init = function() {
        $el.jstree(self.config)
        .on('trees_loaded.jstree', function (event, tree_select_id) {
            if (event.namespace == 'jstree') {
                $('#'+tree_select_id).select2({ width: '100%' });
            }
        })
        .on('after_tree_loaded.jstree', function (event, root_node_id) {
            $(document).one('ajaxStop', function() {
                $el.jstree('create', -1, 'last', {
                    'attr': { 'class': 'jstree-unclassified', 'id': 'node_' },
                    'data': { 'title': _.__('jstree.all') }
                }, null, true);
                $el.jstree('select_node', '#node_');

                $el.jstree('create', '#node_' + root_node_id, 'last', {
                    'attr': { 'class': 'jstree-unclassified', 'id': 'node_0' },
                    'data': { 'title': _.__('jstree.unclassified') }
                }, null, true);
            });
        })
        .on('select_node.jstree', function (event, data) {
            var nodeId = $.jstree._focused().get_selected().attr('id').replace('node_', '');
            var treeId = $('#tree li').first().attr('id').replace('node_', '');
            updateGrid(treeId, nodeId);
        });
    };

    this.init();
};
