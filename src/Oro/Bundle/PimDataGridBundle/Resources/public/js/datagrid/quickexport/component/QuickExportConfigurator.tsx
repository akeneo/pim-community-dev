import React, {ReactNode, useState, Children, isValidElement, cloneElement, useEffect} from 'react';
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
} from '@akeneo-pim-community/shared';
import styled from 'styled-components';

const Container = styled.div`
  color: ${({theme}: AkeneoThemedProps) => theme.color.grey140};
  font-size: ${({theme}: AkeneoThemedProps) => theme.fontSize.default};
  cursor: default;
  text-transform: none;
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

const OptionContainer = styled.div<{isSelected: boolean}>`
  width: 128px;
  padding: 24px 0;
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

const SelectContainer = styled.div`
  display: flex;

  :not(:first-child) {
    margin-top: 40px;
  }
`;

type OptionProps = {
  code: string;
  isSelected?: boolean;
  children?: ReactNode;
  onSelect?: () => void;
};

const Option = ({isSelected, code, children, onSelect}: OptionProps) => {
  const theme = useAkeneoTheme();
  const translate = useTranslate();

  return (
    <OptionContainer isSelected={!!isSelected} onClick={onSelect}>
      {Children.map(children, child => {
        if (!isValidElement<IconProps>(child)) {
          return child;
        }

        return cloneElement<IconProps>(child, {
          color: isSelected ? theme.color.blue100 : child.props.color,
        });
      })}
      {translate(`pim_datagrid.mass_action.quick_export.configurator.${code}`)}
    </OptionContainer>
  );
};

type SelectProps = {
  children?: ReactNode;
  onChange: (value: string | null) => void;
};

const Select = ({onChange, children}: SelectProps) => {
  const [selectedOptionCode, setSelectedOptionCode] = useState<string | null>(null);

  useEffect(() => {
    onChange(selectedOptionCode);
  }, [selectedOptionCode]);

  return (
    <SelectContainer>
      {Children.map(children, child => {
        if (!isValidElement<OptionProps>(child)) {
          return child;
        }

        return cloneElement<OptionProps>(child, {
          isSelected: child.props.code === selectedOptionCode,
          onSelect: () => setSelectedOptionCode(child.props.code),
        });
      })}
    </SelectContainer>
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
  const [type, setType] = useState<string | null>(null);
  const [context, setContext] = useState<string | null>(null);
  const [withLabel, setWithLabel] = useState<string | null>(null);
  const [isModalOpen, openModal, closeModal] = useToggleState(false);
  const translate = useTranslate();

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
                  console.log(type, context, withLabel);
                  onActionLaunch(
                    `quick_export${'grid-context' === context ? `_grid_context` : ''}${
                      'with-labels' === withLabel ? `_with_labels` : ''
                    }_${type}`
                  );
                }}
                disabled={null === type || null === context || null === withLabel}
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
              <Select onChange={setType}>
                <Option code="csv">
                  <CSVFileIcon size={48} />
                </Option>
                <Option code="xlsx">
                  <XLSXFileIcon size={48} />
                </Option>
              </Select>
              {null !== type && (
                <Select onChange={setContext}>
                  <Option code="grid-context" />
                  <Option code="all-attributes" />
                </Select>
              )}
              {null !== context && (
                <Select onChange={setWithLabel}>
                  <Option code="with-codes" />
                  <Option code="with-labels" />
                </Select>
              )}
            </Content>
          </Modal>
        )}
      </Container>
    </>
  );
};

export {QuickExportConfigurator};
