import React from 'react';
import ReactDOM from 'react-dom';
import {ThemeProvider} from 'styled-components';
import {pimTheme} from 'akeneo-design-system';
import {DependenciesProvider} from '@akeneo-pim-community/legacy-bridge';
import {DoubleCheckDeleteModal, DoubleCheckDeleteModalProps} from '@akeneo-pim-community/shared';

const MassAction = require('oro/datagrid/mass-action');
const Routing = require('routing');
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

const GET_FILTERS_ROUTE = 'pim_enrich_mass_edit_rest_get_filter';
const LAUNCH_JOB_ROUTE = 'pim_structure_launch_mass_delete_attribute';
const DELETE_ATTRIBUTES_JOB = 'delete_attributes';

class AttributeMassDeleteAction extends MassAction {
  public readonly identifierFieldName: string = 'code';

  public async execute(): Promise<void> {
    const data = await this.getMassActionData();

    this.openModal(data);
  }

  private openModal(data: MassActionData): void {
    this.renderModal(data, true);
  }

  private disableConfirm(data: MassActionData): void {
    this.renderModal(data, false);
  }

  private closeModal(): void {
    ReactDOM.unmountComponentAtNode(this.el);
  }

  private renderModal(data: MassActionData, canConfirmDelete: boolean): void {
    const textToCheck = translate('pim_common.delete').toLowerCase();

    const modalProps: DoubleCheckDeleteModalProps = {
      onConfirm: () => this.launchJob(data),
      onCancel: this.closeModal.bind(this),
      title: translate('pim_enrich.entity.attribute.module.mass_delete.modal.title'),
      confirmDeletionTitle: translate(
        'pim_enrich.entity.attribute.module.mass_delete.modal.subtitle',
        {
          count: data.itemsCount,
        },
        data.itemsCount
      ),
      children: translate('pim_enrich.entity.attribute.module.mass_delete.modal.confirm'),
      confirmButtonLabel: translate('pim_common.delete'),
      cancelButtonLabel: translate('pim_common.cancel'),
      doubleCheckInputLabel: translate('pim_enrich.entity.attribute.module.mass_delete.modal.label', {textToCheck}),
      textToCheck,
      canConfirmDelete,
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

  private async launchJob(data: MassActionData): Promise<void> {
    this.disableConfirm(data);

    const loadingMask = new LoadingMask();
    loadingMask.render().$el.appendTo($('.hash-loading-mask')).show();

    const url = Routing.generate(LAUNCH_JOB_ROUTE);
    try {
      const response = await fetch(url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          filters: data.filters,
        }),
      });

      if (response.ok) {
        Messenger.notify('success', translate('pim_enrich.entity.attribute.module.mass_delete.message_bar.success'));
      } else {
        Messenger.notify('error', translate('pim_enrich.entity.attribute.module.mass_delete.message_bar.fail'));
      }
    } catch {
      Messenger.notify('error', translate('pim_enrich.entity.attribute.module.mass_delete.message_bar.fail'));
    }

    loadingMask.hide().$el.remove();
    this.closeModal();
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
