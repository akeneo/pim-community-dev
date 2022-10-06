import React from 'react';
import {Deferred} from 'jquery';
import {ThemeProvider} from 'styled-components';
import {pimTheme, Breadcrumb} from 'akeneo-design-system';
import {PageHeader, PageContent, Section} from '@akeneo-pim-community/shared';
import {mountReactElementRef} from './helpers';
import {PimFetcherProvider, TimeToEnrichDashboard} from '@akeneo-pim-enterprise/performance-analytics';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';

const BaseController = require('pim/controller/base');
const mediator = require('oro/mediator');
const translate = require('oro/translator');
const router = require('pim/router');

class Dashboard extends BaseController {
  initialize() {
    // eslint-disable-next-line @typescript-eslint/unbound-method
    mediator.on('route_start', this.handleRouteChange, this);
    mediator.trigger('pim_menu:highlight:tab', {extension: 'pim-menu-activity'});
    mediator.trigger('pim_menu:highlight:item', {extension: 'pim-menu-activity-performance-analytics'});

    return super.initialize();
  }

  renderRoute() {
    this.$el.append(mountReactElementRef(this.reactElementToMount()));
    return Deferred().resolve();
  }

  remove() {
    // eslint-disable-next-line @typescript-eslint/unbound-method
    mediator.off('route_start', this.handleRouteChange, this);
    this.$el.remove();

    return super.remove();
  }

  reactElementToMount() {
    const dashboardHref = router.generate('pim_dashboard_index');

    return (
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <PimFetcherProvider>
            <PageHeader>
              <PageHeader.Breadcrumb>
                <Breadcrumb>
                  <Breadcrumb.Step href={`#${dashboardHref}`}>{translate('pim_menu.tab.activity')}</Breadcrumb.Step>
                  <Breadcrumb.Step>{translate('akeneo.performance_analytics.title')}</Breadcrumb.Step>
                </Breadcrumb>
              </PageHeader.Breadcrumb>
              <PageHeader.Title>{translate('akeneo.performance_analytics.title')}</PageHeader.Title>
            </PageHeader>
            <PageContent>
              <Section>
                <TimeToEnrichDashboard />
              </Section>
            </PageContent>
          </PimFetcherProvider>
        </ThemeProvider>
      </DependenciesProvider>
    );
  }
}

export = Dashboard;
