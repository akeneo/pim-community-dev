import {useTranslate} from '@akeneo-pim-community/shared';
import {useSaveStatus} from 'feature/hooks/useSaveStatus';
import {useContext, useEffect} from 'react';
import {Prompt} from 'react-router';
import {CanLeavePageContext} from './providers';
import {Status} from './providers/SaveStatusProvider';

export const UnsavedChangesGuard = () => {
  const translate = useTranslate();

  const {globalStatus} = useSaveStatus();

  // Browser
  const handleBeforeUnload = (event: BeforeUnloadEvent) => {
    if (globalStatus !== Status.SAVED) {
      event.preventDefault();
      event.returnValue = translate('akeneo.category.template.attribute.settings.unsaved_changes');
    }
  };
  useEffect(() => {
    window.addEventListener('beforeunload', handleBeforeUnload);
    return () => {
      window.removeEventListener('beforeunload', handleBeforeUnload);
    };
  }, [handleBeforeUnload]);

  // Backbone
  const {setCanLeavePage, setLeavePageMessage} = useContext(CanLeavePageContext);
  useEffect(() => {
    if (globalStatus === Status.SAVED) {
      setCanLeavePage(true);
    } else {
      setCanLeavePage(false);
      setLeavePageMessage(translate('akeneo.category.template.attribute.settings.unsaved_changes'));
    }
  }, [globalStatus]);

  // React-router
  return (
    <Prompt
      when={globalStatus !== Status.SAVED}
      message={translate('akeneo.category.template.attribute.settings.unsaved_changes')}
    />
  );
};
