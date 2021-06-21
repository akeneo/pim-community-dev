import React from 'react';
import {Button} from "akeneo-design-system";
import {useRouter, useTranslate} from "@akeneo-pim-community/shared";
import styled from "styled-components";

const StyledButton = styled(Button)`
  margin-right: 20px;
  color: #4ca8e0;
  border: 1px #4ca8e0 solid;
`;

const InviteUserButton = () => {
    const translate = useTranslate();
    const router = useRouter();

    return (
        <StyledButton
            ghost
            level="secondary" onClick={() => {
                // @ts-ignore
                router.redirectToRoute('akeneo_invite_user')
            }}
        >
            {translate('free_trial.invite_users.invite_button')}
        </StyledButton>
    );
};

export {InviteUserButton}
