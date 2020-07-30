import React, {ReactNode, useState} from 'react';
import {DependenciesProvider, useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {
  AkeneoThemeProvider,
  Modal,
  useToggleState,
  ModalCloseButton,
  useShortcut,
  Key,
  ModalConfirmButton,
} from '@akeneo-pim-community/shared';

type OptionProps = {
  code: string;
  isSelected?: boolean;
  onSelect?: () => void;
};

const Option = ({isSelected, code, onSelect}: OptionProps) => {
  return (
    <div style={isSelected ? {background: 'red'} : {}} onClick={onSelect}>
      {code}
    </div>
  );
};

type SelectProps = {
  children?: ReactNode;
};

const Select = ({children}: SelectProps) => {
  const [selectedOptionCode, setSelectedOptionCode] = useState<string | null>(null);

  const updatedChildren = React.Children.map(children, child => {
    if (!React.isValidElement<OptionProps>(child)) {
      return child;
    }

    let elementChild: React.ReactElement<OptionProps> = child;

    if (elementChild.type === 'Option') {
      return React.cloneElement<OptionProps>(elementChild, {
        isSelected: elementChild.props.code === selectedOptionCode,
        onSelect: () => setSelectedOptionCode(elementChild.props.code),
      });
    } else {
      return elementChild;
    }
  });

  return <div>{updatedChildren}</div>;
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

  return (
    <>
      <div onClick={openModal}>{translate('pim_datagrid.mass_action_group.quick_export.label')}</div>
      {isModalOpen && (
        <Modal>
          <ModalCloseButton onClick={() => closeModal()} />
          <ModalConfirmButton disabled={true}>{translate('pim_common.export')}</ModalConfirmButton>
          <div
            onClick={() => {
              closeModal();
              onActionLaunch('yolo');
            }}
          >
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
          </div>
        </Modal>
      )}
    </>
  );
};

export {QuickExportConfigurator};
