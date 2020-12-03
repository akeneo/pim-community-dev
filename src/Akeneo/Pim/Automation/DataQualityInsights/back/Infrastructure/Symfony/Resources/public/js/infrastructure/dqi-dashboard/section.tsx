import ReactDOM from 'react-dom';
import React from 'react';
import {DashboardHelper} from '@akeneo-pim-community/data-quality-insights/src/index';
import {Dashboard} from '@akeneo-pim-ee/data-quality-insights/src/application/component/Dashboard/Dashboard';
import {TimePeriod} from '@akeneo-pim-ee/data-quality-insights/src/domain';

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
          timePeriod={this.timePeriod as TimePeriod}
          catalogLocale={catalogLocale}
          catalogChannel={catalogChannel}
          familyCode={this.familyCode}
          categoryCode={this.categoryCode}
          axes={this.axes}
        />
      </div>,
      this.el
    );
  }
}

export = SectionView;
