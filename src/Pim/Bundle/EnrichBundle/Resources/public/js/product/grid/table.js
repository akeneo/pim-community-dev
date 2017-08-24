/**
 * Wrapper around pim/common/grid to allow use for form extensions.
 * This will be removed when the history and job grids become form extensions.
 *
 * @author    Tamara Robichet <tamara.robichet@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(['pim/common/grid'],
    function (Grid) {
        return Grid.extend({
            initialize(options = {}) {
                return Grid.prototype.initialize.apply(this, [options.config.alias, {}]);
            }
        }
    );
});
