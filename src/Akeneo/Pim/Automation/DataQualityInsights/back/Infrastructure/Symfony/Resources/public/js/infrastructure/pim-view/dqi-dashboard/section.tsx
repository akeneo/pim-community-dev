import BaseView = require('pimui/js/view/base');
import ReactDOM from 'react-dom';
import React from "react";
import {
  DataQualityInsightsDashboard,
  DATA_QUALITY_INSIGHTS_DASHBOARD_CHANGE_PERIODICITY
} from 'akeneodataqualityinsights-react';

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

type DashboardChangePeriodicityEvent = {
  periodicity: string;
};

class SectionView extends BaseView {
  public readonly config: SectionConfig = {
    align: 'left',
  };

  private periodicity: string = 'daily';

  configure(): JQueryPromise<any> {
    window.addEventListener(DATA_QUALITY_INSIGHTS_DASHBOARD_CHANGE_PERIODICITY, ((event: CustomEvent<DashboardChangePeriodicityEvent>) => {
      this.periodicity = event.detail.periodicity;
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
      <DataQualityInsightsDashboard periodicity={this.periodicity} catalogLocale={catalogLocale} catalogChannel={catalogChannel} />,
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
