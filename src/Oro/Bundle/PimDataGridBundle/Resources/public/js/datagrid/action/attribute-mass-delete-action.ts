import React from 'react';
import ReactDOM from 'react-dom';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {DoubleCheckDeleteModal, DoubleCheckDeleteModalProps} from '@akeneo-pim-community/shared';

const MassAction = require('oro/datagrid/mass-action');
const Routing = require('routing');
const Router = require('pim/router');
const Messenger = require('oro/messenger');
const LoadingMask = require('oro/loading-mask');
const translate = require('oro/translator');

type Filter = {
  field: string;
  operator: string;
  values: string[];
};

type MassActionData = {
  filters: Filter[];
  jobInstanceCode: string;
  actions: string[];
  itemsCount: number;
};

const ATTRIBUTE_INDEX_ROUTE = 'pim_enrich_attribute_index';
const GET_FILTERS_ROUTE = 'pim_enrich_mass_edit_rest_get_filter';
const LAUNCH_JOB_ROUTE = 'pim_enrich_mass_edit_rest_launch';
const DELETE_ATTRIBUTES_JOB = 'delete_attributes';

class AttributeMassDeleteAction extends MassAction {
  public readonly identifierFieldName: string;

  constructor(options?: object) {
    super(options);
    this.identifierFieldName = 'code';
  }

  public initialize(options?: object): void {
    super.initialize(options);
  }

  public async execute(): Promise<void> {
    const data = await this.getMassActionData();

    this.openModal(data);
  }

  private openModal(data: MassActionData): void {
    const modalProps: DoubleCheckDeleteModalProps = {
      onConfirm: () => {
        this.launchJob(data);
        this.closeModal();
      },
      onCancel: this.closeModal.bind(this),
      title: translate('pim_enrich.entity.attribute.module.mass_delete.modal.title'),
      confirmDeletionTitle: translate('pim_enrich.entity.attribute.module.mass_delete.modal.subtitle', {
        count: data.itemsCount,
      }),
      children: translate('pim_enrich.entity.attribute.module.mass_delete.modal.confirm'),
      confirmButtonLabel: translate('pim_common.delete'),
      cancelButtonLabel: translate('pim_common.cancel'),
      textToCheck: translate('pim_common.delete').toLowerCase(),
    };

    ReactDOM.render(
      React.createElement(
        ThemeProvider,
        {theme: pimTheme},
        React.createElement(DependenciesProvider, null, React.createElement(DoubleCheckDeleteModal, modalProps))
      ),
      this.el
    );
  }

  private closeModal(): void {
    ReactDOM.unmountComponentAtNode(this.el);
  }

  private async launchJob(data: MassActionData): Promise<void> {
    const loadingMask = new LoadingMask();
    // @TODO Mask seems not working
    loadingMask.render().$el.appendTo($('.hash-loading-mask')).show();

    const url = Routing.generate(LAUNCH_JOB_ROUTE);
    try {
      const response = await fetch(url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(data),
      });

      if (response.ok) {
        Messenger.notify('success', translate('pim_enrich.entity.attribute.module.mass_delete.message_bar.success'));
      } else {
        Messenger.notify('error', translate('pim_enrich.entity.attribute.module.mass_delete.message_bar.fail'));
      }
    } catch {
      Messenger.notify('error', translate('pim_enrich.entity.attribute.module.mass_delete.message_bar.fail'));
    }

    // @TODO Redirect does not work
    Router.redirectToRoute(ATTRIBUTE_INDEX_ROUTE);
    loadingMask.hide().$el.remove();
  }

  private async getMassActionData(): Promise<MassActionData> {
    const actionParameters = this.getOverrideActionParameters();

    const url = Routing.generate(GET_FILTERS_ROUTE);
    const queryParams = new URLSearchParams(actionParameters);

    const response = await fetch(`${url}?${queryParams}`, {
      method: 'POST',
    });
    const {filters, itemsCount} = await response.json();

    return {
      filters,
      jobInstanceCode: DELETE_ATTRIBUTES_JOB,
      actions: [this.getActionName()],
      itemsCount,
    };
  }

  private getOverrideActionParameters() {
    const baseActionParameters = this.getActionParameters();
    return {
      ...baseActionParameters,
      actionName: this.getActionName(),
      gridName: this.getGridName(),
    };
  }

  private getActionName(): string {
    return this.route_parameters['actionName'];
  }

  private getGridName(): string {
    return this.route_parameters['gridName'];
  }
}

export = AttributeMassDeleteAction;
