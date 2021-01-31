import React, {FC} from 'react';
import {ArrowDownIcon, Button, Checkbox, Dropdown, IconButton, Toolbar, useBooleanState} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {AddWordsModal} from '../modals/AddWordsModal';
import styled from 'styled-components';
import {useLocaleSelection} from '../../hooks/locales/useLocaleSelection';

type LocaleToolbarProps = {};

const LocaleToolbar: FC<LocaleToolbarProps> = () => {
  const translate = useTranslate();
  const [isToolbarOpen, openToolbar, closeToolbar] = useBooleanState();
  const [isModalOpen, openModal, closeModal] = useBooleanState();
  const {selectionState, selectedCount, onSelectAllChange} = useLocaleSelection();

  return (
    <Container isVisible={!!selectionState}>
      <Toolbar.SelectionContainer>
        <Checkbox checked={selectionState} onChange={value => onSelectAllChange(value as boolean)} />
        <Dropdown>
          <IconButton
            size="small"
            level="tertiary"
            ghost="borderless"
            icon={<ArrowDownIcon />}
            title="Select"
            onClick={openToolbar}
          />
          {isToolbarOpen && (
            <Dropdown.Overlay onClose={closeToolbar}>
              <Dropdown.Header>
                <Dropdown.Title>{translate('pim_datagrid.select.title')}</Dropdown.Title>
              </Dropdown.Header>
              <Dropdown.ItemCollection>
                <Dropdown.Item
                  onClick={() => {
                    onSelectAllChange(true);
                    closeToolbar();
                  }}
                >
                  {translate('pim_common.all')}
                </Dropdown.Item>
                <Dropdown.Item
                  onClick={() => {
                    onSelectAllChange(false);
                    closeToolbar();
                  }}
                >
                  {translate('pim_common.none')}
                </Dropdown.Item>
              </Dropdown.ItemCollection>
            </Dropdown.Overlay>
          )}
        </Dropdown>
      </Toolbar.SelectionContainer>
      <Toolbar.LabelContainer>
        {translate('pimee_enrich.entity.locale.grid.items_selected', {count: selectedCount}, selectedCount)}
      </Toolbar.LabelContainer>
      <Toolbar.ActionsContainer>
        <Button level="secondary" onClick={openModal}>
          {translate('pimee_enrich.entity.locale.grid.add_words')}
        </Button>
      </Toolbar.ActionsContainer>

      {isModalOpen && <AddWordsModal localesCount={selectedCount} closeModal={closeModal} />}
    </Container>
  );
};

const Container = styled(Toolbar)`
  margin-left: -5px;
`;

export {LocaleToolbar};
