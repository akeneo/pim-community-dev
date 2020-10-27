import React from 'react';
import {ThemeProvider} from 'styled-components';
import {DependenciesProvider, useRoute, useTranslate} from '@akeneo-pim-community/legacy-bridge';
import {ReactView} from '@akeneo-pim-community/legacy-bridge/src/bridge/react';
import {Button, pimTheme} from 'akeneo-design-system';

type StopJobActionProps = {
  id: string;
  isStoppable: boolean;
  refresh: () => void;
};

const StopJobAction = ({id, isStoppable, refresh}: StopJobActionProps) => {
  const translate = useTranslate();
  const stopRoute = useRoute('pim_enrich_job_tracker_rest_stop', {id});

  const stopJob = async () => {
    await fetch(stopRoute);
    refresh();
  };

  if (!isStoppable) return null;

  return (
    <Button onClick={stopJob} level="danger">
      {translate('pim_datagrid.action.stop.title')}
    </Button>
  );
};

class StopJob extends ReactView {
  reactElementToMount() {
    const data = this.getRoot().getFormData();
    const props = {
      id: data.meta.id,
      isStoppable: Boolean(data.isStoppable),
      refresh: () => this.getRoot().render(),
    };

    return (
      <DependenciesProvider>
        <ThemeProvider theme={pimTheme}>
          <StopJobAction {...props} />
        </ThemeProvider>
      </DependenciesProvider>
    );
  }
}

export = StopJob;
