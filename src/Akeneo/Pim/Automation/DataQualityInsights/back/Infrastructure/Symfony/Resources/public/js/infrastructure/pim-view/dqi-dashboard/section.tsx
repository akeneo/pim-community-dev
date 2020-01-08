import BaseView = require('pimui/js/view/base');
import ReactDOM from 'react-dom';
import React from "react";
import {
  DataQualityOverviewCharts,
  DataQualityOverviewHeader
}
  from 'akeneodataqualityinsights-react';

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

class SectionView extends BaseView {
  public readonly config: SectionConfig = {
    align: 'left',
  };

  configure(): JQueryPromise<any> {
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
      <>
        <div>
          <DataQualityOverviewHeader/>
          <DataQualityOverviewCharts catalogLocale={catalogLocale} catalogChannel={catalogChannel}/>
        </div>
      </>,
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
