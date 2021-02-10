import React from 'react';
import {useToggleState} from '@akeneo-pim-community/shared';
import {DuplicateUserApp} from '@akeneo-pim-community/user-ui';

const Router = require('pim/router');
const translate = require('oro/translator');

type DuplicateActionProps = {
  userId: number;
};

const DuplicateOption = ({userId}: DuplicateActionProps) => {
  const [isAppOpened, openApp, closeApp] = useToggleState(false);

  const onDuplicateSuccess = (duplicatedUserId: string) => {
    Router.redirect(Router.generate('pim_user_edit', {identifier: duplicatedUserId}));
  };

  return (
    <>
      <button className="AknDropdown-menuLink duplicate" onClick={openApp}>
        {translate('pim_common.duplicate')}
      </button>
      {isAppOpened && <DuplicateUserApp userId={userId} onCancel={closeApp} onDuplicateSuccess={onDuplicateSuccess} />}
    </>
  );
};

export {DuplicateOption};
