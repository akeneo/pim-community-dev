import {FAMILY_EDIT_FORM_ATTRIBUTES_TAB} from '../constant';

const router = require('pim/router');

const followNotApplicableEnrichmentImageRecommendation = (family: string | null) => {
  if (family === null) {
    return;
  }

  sessionStorage.setItem('current_form_tab', FAMILY_EDIT_FORM_ATTRIBUTES_TAB);

  router.redirectToRoute('pim_enrich_family_edit', {
    code: family,
  });
};

export {followNotApplicableEnrichmentImageRecommendation};
