/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

import BaseView = require('pimui/js/view/base');

const __ = require('oro/translator');
const FetcherRegistry = require('pim/fetcher-registry');
const i18n = require('pim/i18n');
const UserContext = require('pim/user-context');

interface Config {
  title: string;
}

interface Family {
  code: string;
  labels: Array<{ [localeCode: string]: string }>;
}

/**
 * @author Paul Chasle <paul.chasle@akeneo.com>
 */
class Title extends BaseView {
  private config: Config;

  constructor(options: { config: Config }) {
    super({ ...options });

    this.config = { ...this.config, ...options.config };
  }

  public render(): BaseView {
    const data: { code: string } = this.getFormData();

    FetcherRegistry.getFetcher('family')
    .fetch(data.code)
    .then((family: Family) => {
      const familyLabel = i18n.getLabel(family.labels, UserContext.get('catalogLocale'), family.code);

      this.$el.text(`${familyLabel} ${__(this.config.title)}`);
    });

    return this;
  }
}

export = Title;
