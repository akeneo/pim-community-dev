import {CompareApp} from './compare/App';

const BaseForm = require('pim/form');

class CompareView extends BaseForm {
  render() {

    /*AttributeGroupManager.getAttributeGroupsForObject(data)
    .then(attributeGroups => {
      console.log('attributes::attributeGroupManager', attributeGroups, data);
      this.getExtension('attribute-group-selector').setElements(_.indexBy(attributeGroups, 'code'));
      FieldManager.clearVisibleFields();
    })
    .then(() => this.filterValues(data.values))
    .then(values => this.createFields(data, values))
    .then(fields => {
      this.rendering = false;
      $.when(AttributeGroupManager.getAttributeGroupsForObject(data))
      .then(attributeGroups => {

        const scope = UserContext.get('catalogScope');
        const locale = UserContext.get('catalogLocale');
        const fieldsToFill = toFillFieldProvider.getMissingRequiredFields(data, scope, locale);

        const sections = _.values(fields.reduce(groupFieldsBySection(attributeGroups, fieldsToFill), {})).sort(
          (firstSection, secondSection) =>
            firstSection.attributeGroup.sort_order - secondSection.attributeGroup.sort_order
        );
        const fieldsView = document.createElement('div');

        for (const section of sections) {
          fieldsView.appendChild(
            createSectionView(
              section,
              this.attributeGroupTemplate,
              i18n.getLabel(
                section.attributeGroup.labels,
                UserContext.get('catalogLocale'),
                section.attributeGroup.code
              )
            )
          );
        }

        const objectValuesDom = this.$('.object-values').empty();
        if (_.isEmpty(fields)) {
          objectValuesDom.append(
            this.noDataTemplate({
              hint: __('pim_datagrid.no_results', {
                entityHint: __('pim_enrich.entity.attribute.label'),
              }),
              subHint: 'pim_datagrid.no_results_subtitle',
              imageClass: '',
              __,
            })
          );
        } else {
          objectValuesDom.append(fieldsView);
        }
        this.renderExtensions();
        this.delegateEvents();

        _.defer(this.sticky.bind(this));
      })
      .then(() => {
        this.getRoot().trigger('pim_enrich:form:extension:render:after');
        this.getRoot().trigger('pim_enrich:form:attributes:render:after');
      });
    });*/

    this.renderReact(CompareApp, {
      product: this.getFormData(),
    }, this.el);

    return this;
  }
}

export = CompareView;
