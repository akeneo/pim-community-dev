/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import {getConnectionStatus} from '../fetcher/franklin-connection';
import ConnectionStatus from '../model/connection-status';
import Axes from '../model/key-figure-axes';

const BaseController = require('pim/controller/front');
const FetcherRegistry = require('pim/fetcher-registry');
const FormBuilder = require('pim/form-builder');

/**
 * @author Willy MESNAGE <willy.mesnage@akeneo.com>
 */
class KeyFiguresController extends BaseController {
  /**
   * {@inheritdoc}
   */
  public renderForm(): any {
    return getConnectionStatus(false).then((connectionStatus: ConnectionStatus) => {
      if (!connectionStatus.isActive) {
        return;
      }

      return $.when(
        FormBuilder.build('akeneo-franklin-insights-key-figures-index'),
        FetcherRegistry.getFetcher('key-figures').fetchAll()
      ).then((form: any, axes: Axes) => {
        form.setData(axes);

        this.on('pim:controller:can-leave', (event: any) => {
          form.trigger('pim_enrich:form:can-leave', event);
        });
        form.setElement(this.$el).render();

        return form;
      });
    });
  }
}

export = KeyFiguresController;
