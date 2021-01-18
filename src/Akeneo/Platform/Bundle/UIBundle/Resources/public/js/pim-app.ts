import _ from 'underscore';
import Backbone from 'backbone';
import BaseView = require('pimui/js/view/base');

const mediator = require('oro/mediator');
const FetcherRegistry = require('pim/fetcher-registry');
const init = require('pim/init');
const initTranslator = require('pim/init-translator');
const initLayout = require('oro/init-layout');
const initSignin = require('pimuser/js/init-signin');
const pageTitle = require('pim/page-title');
const DateContext = require('pim/date-context');
const UserContext = require('pim/user-context');
const template = require('pim/template/app');
const pimOnBoarding = require('pim/onboarding');

class PimApp extends BaseView {
  private readonly template = _.template(template);

  public events() {
    return {
      'click #overlay': 'onClickToCollapsePanel',
    };
  }

  constructor() {
    super({tagName: 'div', className: 'app'});
  }

  public initialize(): void {
    initLayout();
    initSignin();
  }

  public configure() {
    this.listenTo(mediator, 'pim-app:overlay:show', this.showOverlay);
    this.listenTo(mediator, 'pim-app:overlay:hide', this.hideOverlay);

    return $.when(FetcherRegistry.initialize(), DateContext.initialize(), UserContext.initialize())
      .then(initTranslator.fetch)
      .then(() => {
        pimOnBoarding.registerUser();
      })
      .then(() => {
        init();

        pageTitle.set('Akeneo PIM');

        return super.configure();
      });
  }

  public render(): BaseView {
    this.$el.html(this.template({}));

    if (!Backbone.History.started) {
      Backbone.history.start();
    }

    return BaseView.prototype.render.apply(this, arguments);
  }

  public onClickToCollapsePanel(): void {
    mediator.trigger('pim-app:panel:close');
  }

  private showOverlay(): void {
    this.$('#overlay').addClass('AknOverlay--show');
  }

  private hideOverlay(): void {
    this.$('#overlay').removeClass('AknOverlay--show');
  }
}

export = PimApp;
