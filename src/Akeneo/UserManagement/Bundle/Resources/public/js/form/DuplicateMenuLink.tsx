import React from 'react';
import {useToggleState} from '@akeneo-pim-community/shared';
import {DuplicateUserApp} from '@akeneo-pim-community/user-ui';

const Router = require('pim/router');
const translate = require('oro/translator');

type DuplicateMenuLinkProps = {
  userId: number;
  userCode: string;
};

const DuplicateMenuLink = ({userId, userCode}: DuplicateMenuLinkProps) => {
  const [isAppOpened, openApp, closeApp] = useToggleState(false);

  const onDuplicateSuccess = (duplicatedUserId: number) => {
    Router.redirect(Router.generate('pim_user_edit', {identifier: duplicatedUserId}));
  };

  return (
    <>
      <button className="AknDropdown-menuLink duplicate" onClick={openApp}>
        {translate('pim_common.duplicate')}
      </button>
      {isAppOpened && (
        <DuplicateUserApp
          userId={userId}
          userCode={userCode}
          onCancel={closeApp}
          onDuplicateSuccess={onDuplicateSuccess}
        />
      )}
    </>
  );
};

export {DuplicateMenuLink};
