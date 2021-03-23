import React from 'react';
import {LoaderIcon} from 'akeneo-design-system';
import {LabelCollection, Locale, useIsMounted} from '@akeneo-pim-community/shared';
import {getLabel} from 'pim-community-dev/public/bundles/pimui/js/i18n';
const FetcherRegistry = require('pim/fetcher-registry');
const UserContext = require('pim/user-context');

type ScopeCode = string;

type Scope = {
  code: ScopeCode;
  category_tree: string;
  conversion_units: {[conversion: string]: string};
  currencies: string[];
  labels: LabelCollection;
  locales: Locale[];
  meta: any;
};

type ScopeProps = {
  scopeCode: ScopeCode;
};

const ScopeLabel: React.FC<ScopeProps> = ({scopeCode}) => {
  const [scope, setScope] = React.useState<Scope>();
  const isMounted = useIsMounted();

  React.useEffect(() => {
    FetcherRegistry.initialize().then(() => {
      FetcherRegistry.getFetcher('channel')
        .fetch(scopeCode)
        .then((scope: Scope) => {
          if (isMounted) {
            setScope(scope);
          }
        });
    });
  }, []);

  if (!scope) {
    return <LoaderIcon />;
  }

  return <>{getLabel(scope.labels, UserContext.get('uiLocale'), scope.code)}</>;
};

export {ScopeLabel};
