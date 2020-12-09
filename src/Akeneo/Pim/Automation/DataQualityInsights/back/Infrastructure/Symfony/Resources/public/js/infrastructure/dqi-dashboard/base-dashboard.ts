import BaseView = require('pimui/js/view/base');

import {
  DATA_QUALITY_INSIGHTS_DASHBOARD_CHANGE_TIME_PERIOD,
  DATA_QUALITY_INSIGHTS_DASHBOARD_FILTER_CATEGORY,
  DATA_QUALITY_INSIGHTS_DASHBOARD_FILTER_FAMILY,
} from '@akeneo-pim-community/data-quality-insights/src';
import ReactDOM from 'react-dom';
import {TimePeriod} from '@akeneo-pim-community/data-quality-insights';

interface SectionConfig {
  align: string;
}

interface LocaleEvent {
  localeCode: string;
}

interface ScopeEvent {
  scopeCode: string;
}

type DashboardChangeTimePeriodEvent = {
  timePeriod: string;
};

type DashboardFilterOnFamilyEvent = {
  familyCode: string;
};

type DashboardFilterOnCategoryEvent = {
  categoryCode: string;
  categoryId: string;
  rootCategoryId: string;
};

class BaseDashboard extends BaseView {
  public readonly config: SectionConfig = {
    align: 'left',
  };

  protected timePeriod: TimePeriod = 'weekly';

  protected familyCode: string | null = null;

  protected categoryCode: string | null = null;
  protected categoryId: string | null = null;
  protected rootCategoryId: string | null = null;

  protected readonly axes = [];

  constructor(options: any) {
    super(options);

    this.axes = options.config.axes;
  }

  configure(): JQueryPromise<any> {
    window.addEventListener(DATA_QUALITY_INSIGHTS_DASHBOARD_CHANGE_TIME_PERIOD, ((
      event: CustomEvent<DashboardChangeTimePeriodEvent>
    ) => {
      this.timePeriod = event.detail.timePeriod as TimePeriod;
      this.render();
    }) as EventListener);

    window.addEventListener(DATA_QUALITY_INSIGHTS_DASHBOARD_FILTER_FAMILY, ((
      event: CustomEvent<DashboardFilterOnFamilyEvent>
    ) => {
      this.familyCode = event.detail.familyCode;
      this.categoryCode = null;
      this.categoryId = null;
      this.rootCategoryId = null;
      this.render();
    }) as EventListener);

    window.addEventListener(DATA_QUALITY_INSIGHTS_DASHBOARD_FILTER_CATEGORY, ((
      event: CustomEvent<DashboardFilterOnCategoryEvent>
    ) => {
      this.categoryCode = event.detail.categoryCode;
      this.categoryId = event.detail.categoryId;
      this.rootCategoryId = event.detail.rootCategoryId;
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

  remove() {
    ReactDOM.unmountComponentAtNode(this.el);

    return super.remove();
  }
}

export = BaseDashboard;
