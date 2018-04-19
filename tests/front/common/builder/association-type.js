const createLabels = ({
    locales = {},
    addPrefix = true,
    addChangeset = false
}) => {
    const labels = {};
    const prefix = addPrefix ? 'label-' : '';

    Object.keys(locales).forEach(locale => {
      const labelName = prefix + locale;
      const value = locales[locale];
      labels[labelName] = addChangeset ? { old: value, 'new': value } : value;
    });

    return labels;
}

/**
 * Generate an association type
 *
 * Example:
 *
 * const associationTypeBuilder = require('../../common/builder/association-type');
 * const associationType = (new associationTypeBuilder()).setCode('Similar').setLabels({ en_AU: 'Simila' }).build();
 */
class AssociationTypeBuilder {
  constructor() {
    this.code = 'code';
    this.locales = {
      en_US: 'Type description',
      fr_FR: 'Description de type',
      de_DE: 'Type'
    };
    this.author = 'System';
    this.updatedDate = new Date();
    this.id = '123-456';
    this.labels = createLabels({ locales, addPrefix: false });
    this.labelSnapshot = createLabels({ locales, addChangeset: true });
    this.labelChangeset = createLabels({ locales });
  }

  setCode(code) {
    this.code = code;
  }

  setLabels(labels) {
    this.labels = labels;
  }

  build() {
    return {
      this.code,
      this.labels,
      meta: {
        id,
        form: 'pim-association-type-edit-form',
        model_type: 'association_type',
        created: {
          this.id,
          this.author,
          resource_id: this.id,
          snapshot: { this.code, ...this.labelSnapshot },
          changeset: {
            code: { old: '', new: this.code },
            ...this.labelChangeset
          },
          context: null,
          version: 1,
          logged_at: this.updatedDate,
          pending: false
        },
        updated: {
          this.id,
          this.author,
          resource_id: this.id,
          snapshot: { this.code, ...this.labelSnapshot },
          changeset: {
            code: { old: '', 'new': this.code },
            ...this.labelChangeset
          },
          context: null,
          version: 1,
          logged_at: this.updatedDate,
          pending: false
        }
      }
    };
  }
}
