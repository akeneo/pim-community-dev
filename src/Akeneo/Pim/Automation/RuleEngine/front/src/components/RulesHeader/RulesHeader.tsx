import React, { ReactElement } from 'react';
import styled from 'styled-components';
import { Header } from '../Header';
import { PrimaryButton } from '../Buttons/PrimaryButton';
import { PimView } from '../../dependenciesTools/components/PimView';
import { Breadcrumb } from '../Breadcrumb';
import { UnsavedChangesWarning } from '../UnsavedChangesWarning';
import { useTranslate } from '../../dependenciesTools/hooks';

const IS_DESCRIPTION_HEADER_HIDDEN_KEY =
  'akeneopimruleengine:is-description-header-hidden';

const BreadcrumbAndButtons = styled.div`
  display: inline-flex;
  justify-content: space-between;
  width: 100%;
`;

const DescriptionHeader = styled.div`
  margin: 40px 0 0;
  position: relative;
`;

const Illustration = styled.div`
  background-image: url('/bundles/akeneopimruleengine/assets/illustrations/rules.svg');
`;

const HideButton = styled.div`
  position: absolute;
  top: 10px;
  right: 10px;
  cursor: pointer;
  background-image: url('/bundles/akeneopimruleengine/assets/icons/icon-delete-grey100.svg');
  background-position: center;
  width: 10px;
  height: 10px;
`;

type Props = {
  buttonLabel: string;
  formId: string;
  title: string;
  unsavedChanges?: boolean;
  secondaryButton?: ReactElement;
  dropdown?: ReactElement;
};

const RulesHeader: React.FC<Props> = ({
  buttonLabel,
  children,
  formId,
  title,
  unsavedChanges = false,
  secondaryButton,
  dropdown,
}) => {
  const translate = useTranslate();

  const [
    isDescriptionHeaderHidden,
    setIsDescriptionHeaderHidden,
  ] = React.useState<boolean>(
    localStorage.getItem(IS_DESCRIPTION_HEADER_HIDDEN_KEY) === 'true'
  );

  const hideDescriptionHeader = () => {
    localStorage.setItem(IS_DESCRIPTION_HEADER_HIDDEN_KEY, 'true');
    setIsDescriptionHeaderHidden(true);
  };

  return (
    <Header>
      <BreadcrumbAndButtons>
        <Breadcrumb>{children}</Breadcrumb>
        <div className='AknTitleContainer-buttonsContainer'>
          <PimView
            className='AknTitleContainer-userMenuContainer AknTitleContainer-userMenu'
            viewName='pim-rule-index-user-navigation'
          />
          <div className='AknTitleContainer-actionsContainer AknButtonList'>
            {dropdown}
            <div className='AknButtonList-item'>{secondaryButton}</div>
            <PrimaryButton form={formId} type='submit'>
              {translate(buttonLabel)}
            </PrimaryButton>
          </div>
        </div>
      </BreadcrumbAndButtons>
      <div className='AknTitleContainer-line'>
        <div className='AknTitleContainer-title' data-testid='rule-title'>
          {title}
        </div>
        {unsavedChanges && <UnsavedChangesWarning />}
      </div>
      {!isDescriptionHeaderHidden && (
        <DescriptionHeader className='AknDescriptionHeader'>
          <Illustration className='AknDescriptionHeader-icon' />
          <div className='AknDescriptionHeader-title'>
            {translate('pimee_catalog_rule.form.edit.header.welcome')}
            <div className='AknDescriptionHeader-description'>
              {translate('pimee_catalog_rule.form.edit.header.description')}
              <br />
              <a
                href='https://help.akeneo.com/pim/serenity/articles/get-started-with-the-rules-engine.html'
                target='_blank'
                rel='noopener noreferrer'
                className='AknDescriptionHeader-link'>
                {translate(
                  'pimee_catalog_rule.form.edit.header.documentation_link'
                )}
              </a>
            </div>
          </div>
          <HideButton onClick={hideDescriptionHeader} />
        </DescriptionHeader>
      )}
    </Header>
  );
};

RulesHeader.displayName = 'RulesHeader';

export { RulesHeader };
