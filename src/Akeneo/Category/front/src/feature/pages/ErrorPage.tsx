import {PageContent, PageHeader, useTranslate} from '@akeneo-pim-community/shared';
import {ClientErrorIllustration, Placeholder} from 'akeneo-design-system';
import {useEffect} from 'react';
import {ForbiddenError, UnauthorizedError} from '../tools/apiFetch';

type Props = {
  error: unknown;
};

export const ErrorPage = ({error}: Props) => {
  const translate = useTranslate();

  useEffect(() => {
    // Reload to force the user to log in again
    if (error instanceof UnauthorizedError) {
      globalThis.location.reload();
      return;
    }

    // Reload to force the refresh of the user's permissions
    if (error instanceof ForbiddenError) {
      globalThis.location.reload();
      return;
    }
  }, [error]);

  return (
    <>
      <PageHeader />
      <PageContent>
        <Placeholder
          illustration={<ClientErrorIllustration />}
          size="large"
          title={translate('akeneo.category.unknown_error.title')}
        >
          {translate('akeneo.category.unknown_error.message')}
        </Placeholder>
      </PageContent>
    </>
  );
};
