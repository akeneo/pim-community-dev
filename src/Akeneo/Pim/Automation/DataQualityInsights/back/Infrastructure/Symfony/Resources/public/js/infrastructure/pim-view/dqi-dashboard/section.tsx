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
const UserContext = require('pim/user-context');

class SectionView extends BaseView {
  public readonly config: SectionConfig = {
    align: 'left',
  };

  public render(): BaseView {

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
}

export = SectionView;
