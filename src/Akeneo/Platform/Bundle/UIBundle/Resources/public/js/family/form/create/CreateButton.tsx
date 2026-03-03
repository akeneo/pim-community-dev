import React, {useCallback} from 'react';
import {ArrowDownIcon, Button, Dropdown, useBooleanState} from 'akeneo-design-system';
import {useTranslate, useFeatureFlags} from '@akeneo-pim-community/shared';
import {FamilyTemplateSelector} from '../template/FamilyTemplateSelector';
import {CreateForm} from './CreateForm';

const CreateButton = () => {
  const [isOpenDropdown, openDropdown, closeDropdown] = useBooleanState();
  const [isOpenTemplateSelector, openTemplateSelector, closeTemplateSelector] = useBooleanState();
  const [isOpenCreateForm, openCreateForm, closeCreateForm] = useBooleanState();

  const translate = useTranslate();
  const {isEnabled} = useFeatureFlags();

  const handleOpenCreationForm = () => {
    closeDropdown();
    openCreateForm();
  };

  const handleOpenTemplateSelector = useCallback(() => {
    closeDropdown();
    openTemplateSelector();
  }, [isOpenTemplateSelector, isOpenDropdown]);

  return (
    <>
      <Dropdown>
        {isEnabled('family_template') ? (
          <Button onClick={openDropdown}>
            {translate('pim_enrich.entity.family.module.create.button')}&nbsp;
            <ArrowDownIcon />
          </Button>
        ) : (
          <Button onClick={handleOpenCreationForm}>{translate('pim_enrich.entity.family.module.create.button')}</Button>
        )}
        {isOpenDropdown && (
          <Dropdown.Overlay verticalPosition="down" onClose={closeDropdown}>
            <Dropdown.Header>
              <Dropdown.Title>{translate('pim_enrich.entity.family.module.create.button')}</Dropdown.Title>
            </Dropdown.Header>
            <Dropdown.ItemCollection>
              <Dropdown.Item key="from_scratch" onClick={handleOpenCreationForm}>
                {translate('pim_enrich.entity.family.module.create.from_scratch')}
              </Dropdown.Item>
              <Dropdown.Item key="browse_templates" onClick={handleOpenTemplateSelector}>
                {translate('pim_enrich.entity.family.module.create.browse_templates')}
              </Dropdown.Item>
            </Dropdown.ItemCollection>
          </Dropdown.Overlay>
        )}
      </Dropdown>
      {isOpenTemplateSelector && <FamilyTemplateSelector close={closeTemplateSelector} />}
      {isOpenCreateForm && <CreateForm onConfirm={closeCreateForm} onCancel={closeCreateForm} />}
    </>
  );
};

export {CreateButton};
