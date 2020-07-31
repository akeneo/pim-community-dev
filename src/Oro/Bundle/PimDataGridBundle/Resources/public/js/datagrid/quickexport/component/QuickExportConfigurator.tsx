import React, {ReactNode, Children, isValidElement, cloneElement, ReactElement, ReactNodeArray} from 'react';
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
  IconProps,
  useAkeneoTheme,
  CSVFileIcon,
  XLSXFileIcon,
  useStorageState,
} from '@akeneo-pim-community/shared';
import styled from 'styled-components';

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

const Subtitle = styled.div`
  color: ${({theme}: AkeneoThemedProps) => theme.color.purple100};
  font-size: 19px;
  text-transform: uppercase;
`;

const Title = styled.div`
  color: ${({theme}: AkeneoThemedProps) => theme.color.grey140};
  font-size: 38px;
  margin: 10px 0 60px;
`;

const OptionContainer = styled.div<{isSelected: boolean; withIcon: boolean}>`
  width: 128px;
  padding: ${({withIcon}) => (withIcon ? 24 : 12)}px 0;
  height: ${({withIcon}) => (withIcon ? '128px' : 'auto')};
  justify-content: space-around;
  display: flex;
  flex-direction: column;
  align-items: center;
  border: 1px solid;
  border-color: ${({theme, isSelected}: AkeneoThemedProps & {isSelected: boolean}) =>
    isSelected ? theme.color.blue100 : theme.color.grey80};
  background-color: ${({theme, isSelected}: AkeneoThemedProps & {isSelected: boolean}) =>
    isSelected ? theme.color.blue20 : theme.color.white};
  color: ${({theme, isSelected}: AkeneoThemedProps & {isSelected: boolean}) =>
    isSelected ? theme.color.blue100 : 'inherit'};
  cursor: pointer;

  :not(:first-child) {
    margin-left: 20px;
  }
`;

const SelectContainer = styled.div<{isVisible: boolean}>`
  display: flex;
  opacity: ${({isVisible}) => (isVisible ? 1 : 0)};

  :not(:first-child) {
    margin-top: 40px;
  }
`;

type OptionProps = {
  value: string;
  isSelected?: boolean;
  children?: ReactNode;
  onSelect?: () => void;
};

const Option = ({isSelected, children, onSelect}: OptionProps) => {
  const theme = useAkeneoTheme();
  const withIcon = Children.toArray(children).some((child: ReactNode) => isValidElement<IconProps>(child));

  return (
    <OptionContainer withIcon={withIcon} isSelected={!!isSelected} onClick={onSelect}>
      {Children.map(children, child => {
        if (!isValidElement<IconProps>(child)) {
          return child;
        }

        return cloneElement<IconProps>(child, {
          color: isSelected ? theme.color.blue100 : child.props.color,
        });
      })}
    </OptionContainer>
  );
};

type SelectProps = {
  children?: ReactNode;
  name: string;
  value?: string | null;
  isVisible?: boolean;
  onChange?: (value: string | null) => void;
};

const Select = ({value, onChange, isVisible, children}: SelectProps) => {
  return (
    <SelectContainer isVisible={!!isVisible}>
      {Children.map(children, child => {
        if (!isValidElement<OptionProps>(child)) {
          return child;
        }

        return cloneElement<OptionProps>(child, {
          isSelected: child.props.value === value,
          onSelect: () => undefined !== onChange && onChange(child.props.value),
        });
      })}
    </SelectContainer>
  );
};

type FormValue = {
  [key: string]: string;
};

type FormProps = {
  children?: ReactNode;
  value: FormValue;
  onChange: (value: FormValue) => void;
};

const Form = ({value, onChange, children}: FormProps) => {
  return (
    <>
      {Children.map(children, (child, index) => {
        if (!isValidElement<SelectProps>(child)) {
          return child;
        }

        const options = children as ReactNodeArray;
        const previousOption = options[index - 1] as ReactElement<SelectProps>;

        return cloneElement<SelectProps>(child, {
          onChange: (newValue: string) => {
            onChange({...value, [child.props.name]: newValue});
          },
          isVisible: !(index > 0 && undefined !== previousOption && undefined === value[previousOption.props.name]),
          value: undefined !== value[child.props.name] ? value[child.props.name] : null,
        });
      })}
    </>
  );
};

type QuickExportConfiguratorProps = {
  onActionLaunch: (actionName: string) => void;
};

const QuickExportConfigurator = (props: QuickExportConfiguratorProps) => (
  <DependenciesProvider>
    <AkeneoThemeProvider>
      <QuickExportConfiguratorContainer {...props} />
    </AkeneoThemeProvider>
  </DependenciesProvider>
);

const QuickExportConfiguratorContainer = ({onActionLaunch}: QuickExportConfiguratorProps) => {
  const [isModalOpen, openModal, closeModal] = useToggleState(false);
  const translate = useTranslate();
  const [formValue, setFormValue] = useStorageState({}, 'quick_export_configuration');

  useShortcut(Key.Escape, closeModal);

  //TODO
  const productCount = 3;

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
                  closeModal();

                  onActionLaunch(
                    `quick_export${'grid-context' === formValue['context'] ? `_grid_context` : ''}${
                      'with-labels' === formValue['with-labels'] ? `_with_labels` : ''
                    }_${formValue['type']}`
                  );
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
