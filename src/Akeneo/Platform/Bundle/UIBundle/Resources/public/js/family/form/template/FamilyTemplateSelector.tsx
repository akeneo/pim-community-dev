import React, {FC} from 'react';
import {Modal} from 'akeneo-design-system';
import {useTranslate} from '@akeneo-pim-community/shared';

type Props = {
  close: () => void;
};

const FamilyTemplateSelector: FC<Props> = ({close}) => {
  const translate = useTranslate();

  return <Modal id="template-selector" onClose={close} closeTitle={translate('pim_common.cancel')}></Modal>;
};

export {FamilyTemplateSelector};
