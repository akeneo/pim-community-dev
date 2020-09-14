import BaseView = require('pimui/js/view/base');
import ReactDOM from 'react-dom';
import React from "react";
import {
  Dashboard,
  DashboardHelper,
  DATA_QUALITY_INSIGHTS_DASHBOARD_CHANGE_TIME_PERIOD,
  DATA_QUALITY_INSIGHTS_DASHBOARD_FILTER_FAMILY,
  DATA_QUALITY_INSIGHTS_DASHBOARD_FILTER_CATEGORY
} from '@akeneo-pim-community/data-quality-insights/src/index';

interface SectionConfig {
  align: string;
}
interface LocaleEvent {
  localeCode: string;
}
interface ScopeEvent {
  scopeCode: string;
}

const UserContext = require('pim/user-context');

type DashboardChangeTimePeriodEvent = {
  timePeriod: string;
};

type DashboardFilterOnFamilyEvent = {
  familyCode: string;
};

type DashboardFilterOnCategoryEvent = {
  categoryCode: string;
};

class SectionView extends BaseView {
  public readonly config: SectionConfig = {
    align: 'left',
  };

  private timePeriod: string = 'daily';

  private familyCode: string | null = null;

  private categoryCode: string | null = null;

  private readonly axes = [];

  constructor(options: any) {
    super(options);

    this.axes = options.config.axes;
  }

  configure(): JQueryPromise<any> {
    window.addEventListener(DATA_QUALITY_INSIGHTS_DASHBOARD_CHANGE_TIME_PERIOD, ((event: CustomEvent<DashboardChangeTimePeriodEvent>) => {
      this.timePeriod = event.detail.timePeriod;
      this.render();
    }) as EventListener);

    window.addEventListener(DATA_QUALITY_INSIGHTS_DASHBOARD_FILTER_FAMILY, ((event: CustomEvent<DashboardFilterOnFamilyEvent>) => {
      this.familyCode = event.detail.familyCode;
      this.categoryCode = null;
      this.render();
    }) as EventListener);

    window.addEventListener(DATA_QUALITY_INSIGHTS_DASHBOARD_FILTER_CATEGORY, ((event: CustomEvent<DashboardFilterOnCategoryEvent>) => {
      this.categoryCode = event.detail.categoryCode;
      this.familyCode = null;
      this.render();
    }) as EventListener);

    this.listenTo(this.getRoot(), 'pim_enrich:form:locale_switcher:change', (_: LocaleEvent) => {
      this.render();
    });
    this.listenTo(this.getRoot(), 'pim_enrich:form:scope_switcher:change', (_: ScopeEvent) => {
      this.render();
    });

    return BaseView.prototype.configure.apply(this, arguments);
  }

  render(): BaseView {

    const catalogLocale: string = UserContext.get('catalogLocale');
    const catalogChannel: string = UserContext.get('catalogScope');

    ReactDOM.render(
      <div>
        <DashboardHelper/>
        <Dashboard
          timePeriod={this.timePeriod}
          catalogLocale={catalogLocale}
          catalogChannel={catalogChannel}
          familyCode={this.familyCode}
          categoryCode={this.categoryCode}
          axes={this.axes}
        />
      </div>,
      this.el
    );
    return this;
  }

  remove() {
    ReactDOM.unmountComponentAtNode(this.el);

    return super.remove();
  }
}

export = SectionView;
