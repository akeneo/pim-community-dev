import * as Backbone from 'backbone';
import * as _ from 'underscore';
import BaseView = require('pimui/js/view/base');

const __ = require('oro/translator');
const template = require('pim/template/common/section');

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
class SectionView extends BaseView {
  private static HIDDEN = 'hidden';
  readonly template = _.template(template);
  public hideHint: boolean = false;

  readonly config: SectionConfig = {
    hint: {
      code: '',
      title: '',
      link: '',
    },
    title: '',
  };

  /**
   * {@inheritdoc}
   */
  public events(): Backbone.EventsHash {
    return {
      'click .toggle-hint.active-hint': 'closeHint',
      'click .toggle-hint:not(.active-hint)': 'openHint',
    };
  }

  /**
   * {@inheritdoc}
   */
  constructor(options: {config: SectionConfig}) {
    super(options);

    this.config = {...this.config, ...options.config};
    this.hideHint = false;
  }

  /**
   * If the hint key is in localStorage, don't show it on first render
   * @return {Boolean}
   */
  hintIsHidden(): boolean {
    if (localStorage.getItem(this.config.hint.code) !== null) {
      return localStorage.getItem(this.config.hint.code) === SectionView.HIDDEN;
    }

    return this.hideHint;
  }

  /**
   * {@inheritdoc}
   */
  render(): BaseView {
    this.$el.empty().html(
      this.template({
        title: __(this.config.title),
        hintTitle: __(this.config.hint.title).replace('{{link}}', this.config.hint.link),
        hintIsHidden: this.hintIsHidden(),
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
  closeHint(): void {
    localStorage.setItem(this.config.hint.code, SectionView.HIDDEN);
    this.hideHint = true;
    this.render();
  }

  /**
   * Open the hint box
   */
  openHint(): void {
    localStorage.removeItem(this.config.hint.code);
    this.hideHint = false;
    this.render();
  }
}

export = SectionView;
