import React from 'react';
import {Button} from 'akeneo-design-system';
import {useRouter, useSecurity, useTranslate} from '@akeneo-pim-community/shared';

const MAX_ATTRIBUTE_GROUPS = 1000;

type AttributeGroupsCreateButtonProps = {
  attributeGroupCount: number;
};

const AttributeGroupsCreateButton = ({attributeGroupCount}: AttributeGroupsCreateButtonProps) => {
  const {isGranted} = useSecurity();
  const router = useRouter();
  const translate = useTranslate();

  if (!isGranted('pim_enrich_attributegroup_create')) {
    return null;
  }

  const handleCreateAttributeGroup = () => {
    router.redirectToRoute('pim_enrich_attributegroup_create');
  };

  const limitIsReached = MAX_ATTRIBUTE_GROUPS <= attributeGroupCount;

  return (
    <Button
      onClick={handleCreateAttributeGroup}
      level="primary"
      disabled={limitIsReached}
      title={
        limitIsReached
          ? translate('pim_enrich.entity.attribute_group.limit_reached', {max: MAX_ATTRIBUTE_GROUPS})
          : undefined
      }
    >
      {translate('pim_common.create')}
    </Button>
  );
};

export {AttributeGroupsCreateButton};
