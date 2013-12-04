define(['oro/navigation/dotmenu/view-orig', 'oro/mediator'],
    function(OroDotmenuView, mediator) {
        return OroDotmenuView.extend({
            initialize: function() {
                this.$tabsContainer = this.$('.pin-menus .nav-tabs');
                this.$tabsContent = this.$('.pin-menus .tab-content');
                this.init();
                mediator.bind(
                    "hash_navigation_request:complete",
                    function() {
                        this.init();
                    },
                    this
                );
                mediator.bind(
                    "tab:changed",
                    function(tabId) {
                        this.chooseActiveTab(tabId);
                    },
                    this
                );
                this.chooseActiveTab();
            }
        })
    }
)