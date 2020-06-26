import React from 'react';
import { ThemeProvider } from 'styled-components';
import * as akeneoTheme from '../../theme';
import {
  generateAndRedirect,
  generateUrl,
  NotificationLevel,
  redirectToUrl,
  useBackboneRouter,
  useNotify,
  useTranslate,
  useUserContext,
} from '../../dependenciesTools/hooks';
import { useDocumentEscapeKey } from '../../hooks';
import { CrossLink } from './components/CrossLink';
import { CreateRulesForm, FormDataInput } from './components/CreateRulesForm';
import { httpPost } from '../../fetch';
import { AkeneoSpinner, SmallHelper } from '../../components';
import { Payload } from '../../rules.types';
import { LocaleCode } from '../../models';
import illustrationUrl from '../../assets/illustrations/rules.svg';

const transformFormData = (
  formData: FormDataInput,
  currentCatalogLocale: LocaleCode
): Payload => {
  return {
    labels: { [currentCatalogLocale]: formData.label },
    priority: 0,
    code: formData.code,
    content: {
      conditions: {},
      actions: {},
    },
  };
};

const CreateRules: React.FC = () => {
  const [pending, setPending] = React.useState(false);
  const translate = useTranslate();
  const router = useBackboneRouter();
  const userContext = useUserContext();
  const [urlRules, handleRulesRoute] = generateAndRedirect(
    router,
    'pimee_catalog_rule_rule_index'
  );
  useDocumentEscapeKey(handleRulesRoute);
  const currentCatalogLocale = userContext.get('catalogLocale');
  const notify = useNotify();
  const onSubmit = async (formData: FormDataInput): Promise<any> => {
    const postRule = generateUrl(router, 'pimee_enrich_rule_definition_create');
    setPending(true);
    let result: any;
    try {
      result = await httpPost(postRule, {
        body: transformFormData(formData, currentCatalogLocale),
      });
    } catch (error) {
      setPending(false);
      notify(
        NotificationLevel.ERROR,
        translate('pimee_catalog_rule.form.creation.notification.failed')
      );
      return error;
    }
    if (result.ok) {
      notify(
        NotificationLevel.SUCCESS,
        translate('pimee_catalog_rule.form.creation.notification.success')
      );
      redirectToUrl(
        router,
        generateUrl(router, 'pimee_catalog_rule_edit', { code: formData.code })
      );
    } else {
      setPending(false);
      notify(
        NotificationLevel.ERROR,
        translate('pimee_catalog_rule.form.creation.notification.failed')
      );
    }
    return result;
  };

  return (
    <ThemeProvider theme={akeneoTheme}>
      <div className='AknFullPage'>
        {pending && <AkeneoSpinner />}
        <div className='AknFullPage-content AknFullPage-content--withIllustration'>
          <div>
            <div
              className='AknFullPage-image AknFullPage-illustration'
              style={{ backgroundImage: `url('${illustrationUrl}')` }}
            />
          </div>
          <div>
            <div className='AknFullPage-titleContainer'>
              <div className='AknFullPage-subTitle'>
                {`${translate('pim_menu.item.rule')} /`}
              </div>
              <div className='AknFullPage-title'>
                {translate('pimee_catalog_rule.form.creation.title')}
              </div>
              <SmallHelper>
                <span>
                  {translate('pimee_catalog_rule.form.creation.helper')}
                  &nbsp;
                  <a href='#'>Link to documentation under construction</a>
                </span>
              </SmallHelper>
            </div>
            <CreateRulesForm
              onSubmit={onSubmit}
              translate={translate}
              locale={currentCatalogLocale}
            />
          </div>
        </div>
      </div>
      <CrossLink
        data-testid='leave-page-button'
        href={`#${urlRules}`}
        onClick={handleRulesRoute}>
        {translate('pimee_catalog_rule.form.creation.cross_link')}
      </CrossLink>
    </ThemeProvider>
  );
};

export { CreateRules };
