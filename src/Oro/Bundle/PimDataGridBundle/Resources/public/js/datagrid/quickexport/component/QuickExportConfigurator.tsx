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

const QuickExportButton = styled.button`
  color: inherit;
  padding: 0;
  margin: 0;
  background-color: inherit;
  border: none;
  font-size: inherit;
  text-transform: inherit;
  cursor: pointer;
`;

type QuickExportConfiguratorProps = {
  onActionLaunch: (formValue: FormValue) => void;
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
      <QuickExportButton title={translate('pim_datagrid.mass_action_group.quick_export.label')} onClick={openModal}>
        {translate('pim_datagrid.mass_action_group.quick_export.label')}
      </QuickExportButton>
      {isModalOpen && (
        <Container>
          <Modal>
            <Content>
              <ModalCloseButton title={translate('pim_common.close')} onClick={closeModal} />
              <ModalConfirmButton
                title={translate('pim_common.export')}
                onClick={() => {
                  onActionLaunch(formValue);
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
                  'pim_common.result_count',
                  {itemsCount: productCount.toString()},
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
                  <Option value="csv" title={translate('pim_datagrid.mass_action.quick_export.configurator.csv')}>
                    <CSVFileIcon size={48} />
                  </Option>
                  <Option value="xlsx" title={translate('pim_datagrid.mass_action.quick_export.configurator.xlsx')}>
                    <XLSXFileIcon size={48} />
                  </Option>
                </Select>
                <Select name="context">
                  <Option
                    value="grid-context"
                    title={translate('pim_datagrid.mass_action.quick_export.configurator.grid_context')}
                  />
                  <Option
                    value="all-attributes"
                    title={translate('pim_datagrid.mass_action.quick_export.configurator.all_attributes')}
                  />
                </Select>
                <Select name="with-labels">
                  <Option
                    value="with-codes"
                    title={translate('pim_datagrid.mass_action.quick_export.configurator.with_codes')}
                  />
                  <Option
                    value="with-labels"
                    title={translate('pim_datagrid.mass_action.quick_export.configurator.with_labels')}
                  />
                </Select>
              </Form>
            </Content>
          </Modal>
        </Container>
      )}
    </>
  );
};

export {QuickExportConfigurator};
