import React from 'react';
import {useParams} from 'react-router-dom';
import {EditGeneratorPage} from '../pages/';
import {useGetIdentifierGenerator} from '../hooks';
import {LoaderIcon, Placeholder, ServerErrorIllustration} from 'akeneo-design-system';
import {IdentifierGeneratorNotFound} from '../errors';
import {Styled} from '../components/Styled';
import {useTranslate} from '@akeneo-pim-community/shared';

const Edit: React.FC = () => {
  const translate = useTranslate();
  const {identifierGeneratorCode} = useParams<{identifierGeneratorCode: string}>();
  const {data: identifierGenerator, error} = useGetIdentifierGenerator(identifierGeneratorCode);

  if (error) {
    let title = translate('pim_error.general');
    let subtitle = error?.message;

    if (error instanceof IdentifierGeneratorNotFound) {
      title = translate('pim_error.404');
      subtitle = translate('pim_error.identifier_generator_not_found');
    }

    return (
      <Styled.FullPageCenteredContent>
        <Placeholder illustration={<ServerErrorIllustration />} size="large" title={title}>
          {subtitle}
        </Placeholder>
      </Styled.FullPageCenteredContent>
    );
  }

  if (typeof identifierGenerator === 'undefined') {
    return (
      <Styled.FullPageCenteredContent>
        <LoaderIcon data-testid={'loadingIcon'} />
      </Styled.FullPageCenteredContent>
    );
  }

  return <EditGeneratorPage initialGenerator={identifierGenerator} />;
};

export {Edit};
