import {BACK_LINK_SESSION_STORAGE_KEY, FAMILY_EDIT_FORM_ATTRIBUTES_TAB} from '../constant';
import {Product} from '../../domain';
import {isProductModel} from '../helper';

const router = require('pim/router');
const translate = require('oro/translator');

const followNotApplicableEnrichmentImageRecommendation = (product: Product) => {
  const family: string | null = product.family;
  if (family === null) {
    return;
  }

  window.sessionStorage.setItem(
    BACK_LINK_SESSION_STORAGE_KEY,
    JSON.stringify({
      label: translate('akeneo_data_quality_insights.product_edit_form.back_to_products'),
      route: isProductModel(product) ? 'pim_enrich_product_model_edit' : 'pim_enrich_product_edit',
      routeParams: {id: product.meta.id},
      displayLinkRoutes: ['pim_enrich_family_edit'],
    })
  );

  sessionStorage.setItem('current_form_tab', FAMILY_EDIT_FORM_ATTRIBUTES_TAB);

  router.redirectToRoute('pim_enrich_family_edit', {
    code: family,
  });
};

export {followNotApplicableEnrichmentImageRecommendation};
