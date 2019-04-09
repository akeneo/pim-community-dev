(function($, _) {
    /**
     * Extension of select2 to allow placeholder in the search input field
     * Original solution comes from
     * https://stackoverflow.com/questions/45819164/how-make-select2-placeholder-for-search-input
     *
     * @author    Pierre Allard <pierre.allard@akeneo.com>
     * @author    Tamara Robichet <tamara.noob@akeneo.com>
     * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
     * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
     */
    "use strict";

    let Defaults = $.fn.select2.defaults;
    $.extend(Defaults, {
        searchInputPlaceholder: null
    });

    const createContainer = window.Select2['class']['single'].prototype.createContainer;

    window.Select2['class']['single'].prototype.createContainer = function () {
        const container = createContainer.apply(this, arguments);
        let placeholder = this.opts.searchInputPlaceholder;
        if (placeholder === null) {
            placeholder = _.__('pim_common.search');
        }
        container.find('.select2-input').attr('placeholder', placeholder);

        return container;
    };
})(jQuery, _);
