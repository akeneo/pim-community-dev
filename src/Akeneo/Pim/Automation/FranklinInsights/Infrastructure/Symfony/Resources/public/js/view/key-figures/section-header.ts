import * as Backbone from 'backbone';
import BaseView = require('pimui/js/view/base');
import * as _ from 'underscore';

const __ = require('oro/translator');
const template = require('akeneo/franklin-insights/template/key-figures/section-container');

interface SectionConfig {
  hint: {
    code: string;
    title: string;
    link: string;
  };
  title: string;
}

/**
 * Section view
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SectionHeader extends BaseView {
  private static HIDDEN = 'hidden';
  public readonly template = _.template(template);
  public hideHint: boolean = false;

  public readonly config: SectionConfig = {
    hint: {
      code: '',
      title: '',
      link: ''
    },
    title: ''
  };

  /**
   * {@inheritdoc}
   */
  constructor(options: {config: SectionConfig}) {
    super(options);

    this.config = {...this.config, ...options.config};
    this.hideHint = false;
  }

  /**
   * {@inheritdoc}
   */
  public events(): Backbone.EventsHash {
    return {
      'click .toggle-hint.active-hint': 'closeHint',
      'click .toggle-hint:not(.active-hint)': 'openHint'
    };
  }

  /**
   * {@inheritdoc}
   */
  public render(): BaseView {
    this.$el.empty().html(
      this.template({
        title: __(this.config.title),
        hint: this.getHint()
      })
    );

    this.renderExtensions();

    if (this.$el.find('[data-drop-zone="content"]').html().length === 0) {
      // Remove the complete section if there is no content to display.
      this.$el.empty();
    }

    return this;
  }

  /**
   * Close the hint box and store the key in localStorage
   */
  public closeHint(): void {
    localStorage.setItem(this.config.hint.code, SectionHeader.HIDDEN);
    this.hideHint = true;
    this.render();
  }

  /**
   * Open the hint box
   */
  public openHint(): void {
    localStorage.removeItem(this.config.hint.code);
    this.hideHint = false;
    this.render();
  }

  /**
   * If the hint key is in localStorage, don't show it on first render
   * @return {Boolean}
   */
  private hintIsHidden(): boolean {
    if (localStorage.getItem(this.config.hint.code) !== null) {
      return localStorage.getItem(this.config.hint.code) === SectionHeader.HIDDEN;
    }

    return this.hideHint;
  }

  private getHint(): {title: string; isHidden: boolean} | false {
    if ('' === this.config.hint.title) {
      return false;
    }

    return {
      title: __(this.config.hint.title).replace('{{link}}', this.config.hint.link),
      isHidden: this.hintIsHidden()
    };
  }
}

export = SectionHeader;
