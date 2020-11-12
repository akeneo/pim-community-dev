const router = require('pim/router');

const followNotApplicableEnrichmentImageRecommendation = (family: string | null) => {
  if (family === null) {
    return;
  }

  router.redirectToRoute('pim_enrich_family_edit', {
    code: family,
  });
};

export {followNotApplicableEnrichmentImageRecommendation};
