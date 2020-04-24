import {saveFamilyMapping as familyMappingSaver} from '../../infrastructure/saver/family-mapping';
import {notify} from '../action/notify';
import {NotificationLevel} from '../notification-level';
import {translate} from '../../infrastructure/translator';
import {AttributesMapping} from '../../domain/model/attributes-mapping';
import {savedFamilyMappingSuccess, savedFamilyMappingFail} from '../action-creator/family-mapping/save-family-mapping';
import {fetchFamilyMapping} from '../action/family-mapping/family-mapping';

export function saveFamilyMapping(familyCode: string, mapping: AttributesMapping) {
  return async (dispatch: any) => {
    try {
      await familyMappingSaver(familyCode, mapping);
      dispatch(savedFamilyMappingSuccess());
      dispatch(notify(NotificationLevel.SUCCESS, translate('pim_enrich.entity.fallback.flash.update.success')));

      dispatch(fetchFamilyMapping(familyCode));
    } catch (error) {
      dispatch(savedFamilyMappingFail());

      let errorFlashMessage = translate('pim_enrich.entity.fallback.flash.update.fail');
      if (error.status === 400 && error.responseJSON !== undefined) {
        errorFlashMessage = error.responseJSON.map((message: any) => translate(message)).join('. ');
      }

      dispatch(notify(NotificationLevel.ERROR, errorFlashMessage));
    }
  };
}
