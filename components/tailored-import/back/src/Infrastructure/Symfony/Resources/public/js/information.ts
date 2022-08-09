import {createElement} from 'react';
import {Information, JuliaIllustration, Link} from 'akeneo-design-system';
import BaseView = require('pimui/js/view/base');
const translate = require('oro/translator');

type InformationConfig = {
  title: string;
  text: string;
  link?: {
    href: string;
    text: string;
  };
};

class InformationView extends BaseView {
  private config: InformationConfig;

  constructor(options: {config: InformationConfig}) {
    super(options);

    this.config = {...this.config, ...options.config};
  }

  /**
   * {@inheritdoc}
   */
  render(): BaseView {
    const {title, text, link} = this.config;

    const children = link
      ? createElement(
          'div',
          null,
          translate(text),
          createElement('br'),
          createElement(Link, {
            href: link.href,
            children: translate(link.text),
            target: '_blank',
          })
        )
      : translate(text);

    this.renderReact(
      Information,
      {
        illustration: createElement(JuliaIllustration),
        title: translate(title),
        children,
      },
      this.el
    );

    return this;
  }
}

export = InformationView;
