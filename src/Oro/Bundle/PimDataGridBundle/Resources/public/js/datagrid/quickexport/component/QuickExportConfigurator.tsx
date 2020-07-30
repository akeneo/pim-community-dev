import React, {ReactNode, useState, Children, isValidElement, cloneElement} from 'react';
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
} from '@akeneo-pim-community/shared';
import styled from 'styled-components';

const Container = styled.div``;

const Content = styled.div`
  color: ${({theme}: AkeneoThemedProps) => theme.color.grey100};
  display: flex;
  flex-direction: column;
  align-items: center;
  text-transform: none;
  font-size: ${({theme}: AkeneoThemedProps) => theme.fontSize.default};
  cursor: default;
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
  text-align: center;
  border: 1px solid;
  border-color: ${({theme, isSelected}: AkeneoThemedProps & {isSelected: boolean}) =>
    isSelected ? theme.color.blue100 : theme.color.grey80};
  background-color: ${({theme, isSelected}: AkeneoThemedProps & {isSelected: boolean}) =>
    isSelected ? theme.color.blue20 : theme.color.white};
  color: ${({theme, isSelected}: AkeneoThemedProps & {isSelected: boolean}) =>
    isSelected ? theme.color.blue100 : 'inherit'};

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
  onSelect?: () => void;
};

const Option = ({isSelected, code, onSelect}: OptionProps) => {
  return (
    <OptionContainer isSelected={!!isSelected} onClick={onSelect}>
      {code}
    </OptionContainer>
  );
};

type SelectProps = {
  children?: ReactNode;
};

const Select = ({children}: SelectProps) => {
  const [selectedOptionCode, setSelectedOptionCode] = useState<string | null>(null);

  const updatedChildren = Children.map(children, child => {
    if (!isValidElement<OptionProps>(child)) {
      return child;
    }

    if (child.type === Option) {
      return cloneElement<OptionProps>(child, {
        isSelected: child.props.code === selectedOptionCode,
        onSelect: () => setSelectedOptionCode(child.props.code),
      });
    } else {
      return child;
    }
  });

  return <SelectContainer>{updatedChildren}</SelectContainer>;
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

  useShortcut(Key.Escape, closeModal);

  //TODO
  const productCount = 3;

  return (
    <Container>
      <div onClick={openModal}>{translate('pim_datagrid.mass_action_group.quick_export.label')}</div>
      {isModalOpen && (
        <Modal>
          <Content>
            <ModalCloseButton onClick={closeModal} />
            <ModalConfirmButton
              onClick={() => {
                closeModal();
                onActionLaunch('yolo');
              }}
              disabled={true}
            >
              {translate('pim_common.export')}
            </ModalConfirmButton>
            <Subtitle>
              {translate('pim_datagrid.mass_action.quick_export.configurator.subtitle')} |{' '}
              {translate(
                'pim_datagrid.mass_action.quick_export.configurator.product_count',
                {
                  count: productCount.toString(),
                },
                productCount
              )}
            </Subtitle>
            <Title>{translate('pim_datagrid.mass_action.quick_export.configurator.title')}</Title>
            <Select>
              <Option code="csv"></Option>
              <Option code="xlsx"></Option>
            </Select>
            <Select>
              <Option code="grid-context"></Option>
              <Option code="all"></Option>
            </Select>
            <Select>
              <Option code="with-label"></Option>
              <Option code="without-label"></Option>
            </Select>
          </Content>
        </Modal>
      )}
    </Container>
  );
};

export {QuickExportConfigurator};
