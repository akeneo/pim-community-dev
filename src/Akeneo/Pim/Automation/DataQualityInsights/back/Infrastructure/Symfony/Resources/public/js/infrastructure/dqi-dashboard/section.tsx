import ReactDOM from 'react-dom';
import React from 'react';
import {Dashboard, DashboardHelper} from '@akeneo-pim-community/data-quality-insights/src/index';

const UserContext = require('pim/user-context');
const BaseDashboard = require('akeneo/data-quality-insights/view/dqi-dashboard/base-dashboard');

class SectionView extends BaseDashboard {
  render() {
    const catalogLocale: string = UserContext.get('catalogLocale');
    const catalogChannel: string = UserContext.get('catalogScope');

    ReactDOM.render(
      <div>
        <DashboardHelper />
        <Dashboard
          timePeriod={this.timePeriod}
          catalogLocale={catalogLocale}
          catalogChannel={catalogChannel}
          familyCode={this.familyCode}
          categoryCode={this.categoryCode}
          categoryId={this.categoryId}
          rootCategoryId={this.rootCategoryId}
          axes={this.axes}
        />
      </div>,
      this.el
    );
  }
}

export = SectionView;
