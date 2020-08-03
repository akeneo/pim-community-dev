import React from 'react';
import styled from 'styled-components';
import {DependenciesProvider, useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {
  AkeneoThemeProvider,
  Modal,
  useToggleState,
  ModalCloseButton,
  useShortcut,
  Key,
  ModalConfirmButton,
  AkeneoThemedProps,
  CSVFileIcon,
  XLSXFileIcon,
  useStorageState,
} from '@akeneo-pim-community/shared';
import {Form, FormValue} from './Form';
import {Select} from './Select';
import {Option} from './Option';

const Container = styled.div`
  color: ${({theme}: AkeneoThemedProps) => theme.color.grey140};
  font-size: ${({theme}: AkeneoThemedProps) => theme.fontSize.default};
  cursor: default;
  text-transform: none;
  line-height: initial;
`;

const Content = styled.div`
  display: flex;
  flex-direction: column;
  align-items: center;
`;

const Title = styled.div`
  color: ${({theme}: AkeneoThemedProps) => theme.color.grey140};
  font-size: 38px;
  margin: 10px 0 60px;
`;

const Subtitle = styled.div`
  color: ${({theme}: AkeneoThemedProps) => theme.color.purple100};
  font-size: 19px;
  text-transform: uppercase;
`;

type QuickExportConfiguratorProps = {
  onActionLaunch: (actionName: string) => void;
  getProductCount: () => number;
};

const QuickExportConfigurator = (props: QuickExportConfiguratorProps) => (
  <DependenciesProvider>
    <AkeneoThemeProvider>
      <QuickExportConfiguratorContainer {...props} />
    </AkeneoThemeProvider>
  </DependenciesProvider>
);

const QuickExportConfiguratorContainer = ({onActionLaunch, getProductCount}: QuickExportConfiguratorProps) => {
  const [isModalOpen, openModal, closeModal] = useToggleState(false);
  const translate = useTranslate();
  const [formValue, setFormValue] = useStorageState<FormValue>({}, 'quick_export_configuration');

  useShortcut(Key.Escape, closeModal);

  const productCount = getProductCount();

  return (
    <>
      <div onClick={openModal}>{translate('pim_datagrid.mass_action_group.quick_export.label')}</div>
      <Container>
        {isModalOpen && (
          <Modal>
            <Content>
              <ModalCloseButton onClick={closeModal} />
              <ModalConfirmButton
                onClick={() => {
                  const actionName = `quick_export${'grid-context' === formValue['context'] ? `_grid_context` : ''}${
                    'with-labels' === formValue['with-labels'] ? `_with_labels` : ''
                  }_${formValue['type']}`;

                  onActionLaunch(actionName);
                  closeModal();
                }}
                disabled={
                  undefined === formValue['type'] ||
                  undefined === formValue['context'] ||
                  undefined === formValue['with-labels']
                }
              >
                {translate('pim_common.export')}
              </ModalConfirmButton>
              <Subtitle>
                {`${translate('pim_datagrid.mass_action.quick_export.configurator.subtitle')} | ${translate(
                  'pim_datagrid.mass_action.quick_export.configurator.product_count',
                  {count: productCount.toString()},
                  productCount
                )}`}
              </Subtitle>
              <Title>{translate('pim_datagrid.mass_action.quick_export.configurator.title')}</Title>
              <Form
                value={formValue}
                onChange={(newValue: FormValue) => {
                  setFormValue(newValue);
                }}
              >
                <Select name="type">
                  <Option value="csv">
                    <CSVFileIcon size={48} />
                    {translate('pim_datagrid.mass_action.quick_export.configurator.csv')}
                  </Option>
                  <Option value="xlsx">
                    <XLSXFileIcon size={48} />
                    {translate('pim_datagrid.mass_action.quick_export.configurator.xlsx')}
                  </Option>
                </Select>
                <Select name="context">
                  <Option value="grid-context">
                    {translate('pim_datagrid.mass_action.quick_export.configurator.grid_context')}
                  </Option>
                  <Option value="all-attributes">
                    {translate('pim_datagrid.mass_action.quick_export.configurator.all_attributes')}
                  </Option>
                </Select>
                <Select name="with-labels">
                  <Option value="with-codes">
                    {translate('pim_datagrid.mass_action.quick_export.configurator.with_codes')}
                  </Option>
                  <Option value="with-labels">
                    {translate('pim_datagrid.mass_action.quick_export.configurator.with_labels')}
                  </Option>
                </Select>
              </Form>
            </Content>
          </Modal>
        )}
      </Container>
    </>
  );
};

export {QuickExportConfigurator};
