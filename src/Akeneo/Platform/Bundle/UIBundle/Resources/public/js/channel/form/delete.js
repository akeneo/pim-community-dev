'use strict';

/**
 * Delete extension for channel
 *
 * @author    Alexandr Jeliuc <alex@jeliuc.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(['pim/form/common/delete', 'pim/remover/channel'], function (DeleteForm, ChannelRemover) {
    return DeleteForm.extend({
        remover: ChannelRemover
    });
});
