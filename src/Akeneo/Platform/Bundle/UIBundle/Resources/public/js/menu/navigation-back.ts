const {BaseForm: View} = require('pim/form');
const Router = require('pim/router');
const translate = require('oro/translator');

type Config = {
  linkText: string;
  redirectRoute: string;
};

class NavigationBack extends View {
  config: Config;

  initialize({config}: {config: Config}) {
    this.config = config;
  }

  render() {
    this.$el.html(`
        <div class="AknColumn-block">
            <span class="AknColumn-navigationLink navigation-back" tabindex="0" role="button">
                ${translate(this.config.linkText)}
            </span>
        </div>
    `);

    this.delegateEvents({
      'click .navigation-back': this.redirect.bind(this),
    });

    super.render();
  }

  redirect() {
    Router.redirectToRoute(this.config.redirectRoute);
  }
}

export default NavigationBack;
