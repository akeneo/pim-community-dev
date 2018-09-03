'use strict';
/**
 * Download file extension
 *
 * @author    Alban Alnot <alban.alnot@consertotech.pro>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
define(
    [
        'underscore',
        'oro/translator',
        'pim/form',
        'pim/template/job-execution/download-archives-buttons',
        'routing',
        'pim/common/property',
        'pim/security-context'
    ],
    function (_,
              __,
              BaseForm,
              template,
              Routing,
              propertyAccessor,
              securityContext
    ) {
        return BaseForm.extend({
            template: _.template(template),

            /**
             * {@inheritdoc}
             */
            initialize: function (meta) {
                this.config = meta.config;

                BaseForm.prototype.initialize.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            configure: function () {
                this.listenTo(this.getRoot(), 'pim_enrich:form:entity:post_update', this.render);

                return BaseForm.prototype.configure.apply(this, arguments);
            },

            /**
             * {@inheritdoc}
             */
            render: function () {
                if (!this.isVisible()) {
                    return this;
                }
                this.$el.html(this.template({
                    label: this.getLabel(),
                    files: this.getFiles()
                }));

                return this;
            },

            /**
             * Get the main button label. If there is only one file to download, it takes the name of the file.
             *
             * @returns {string}
             */
            getLabel() {
                const formData = this.getFormData();
                const archives = propertyAccessor.accessProperty(formData, this.config.filesPath);

                let files = [];
                Object.values(archives).forEach((archive) => {
                    files = Object.assign({}, files, archive.files);
                });

                if (Object.keys(files).length === 1) {
                    return __(Object.values(archives)[0].label);
                } else {
                    return __('pim_enrich.entity.job_execution.module.download.output');
                }
            },

            /**
             * Get the list of files to download, under the form label => url.
             *
             * @returns {Object}
             */
            getFiles() {
                const formData = this.getFormData();
                const archives = propertyAccessor.accessProperty(formData, this.config.filesPath);

                let files = {};
                Object.keys(archives).forEach((archiver) => {
                    const archive = archives[archiver];
                    let label = null;
                    if (Object.keys(archive.files).length === 1) {
                        label = __(archive.label);
                    }
                    Object.keys(archive.files).forEach((key) => {
                        const archiveLabel = null === label ? key : label;
                        files[archiveLabel] = this.getUrl({id: formData.meta.id, archiver: archiver, key: key });
                    });
                });

                return files;
            },

            /**
             * Get the url from parameters
             *
             * @returns {string}
             */
            getUrl: function (parameters) {
                return Routing.generate(
                    this.config.url,
                    parameters
                );
            },

            /**
             * Returns true if the extension should be visible
             *
             * @returns {boolean}
             */
            isVisible: function () {
                var formData = this.getFormData();
                if (formData.jobInstance.type === 'export') {
                    return securityContext.isGranted(this.config.aclIdExport);
                } else if (formData.jobInstance.type === 'import') {
                    return securityContext.isGranted(this.config.aclIdImport);
                } else {
                    return true;
                }
            }
        });
    }
);
