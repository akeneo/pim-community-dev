'use strict';

/**
 * Displays the project resume in the main column
 *
 * @author Pierre Allard <pierre.allard@akeneo.com>
 */
define(
    [
        'jquery',
        'underscore',
        'oro/translator',
        'pim/form',
        'teamwork-assistant/templates/grid/view-selector/project-resume',
        'pim/formatter/date',
        'pim/date-context'
    ],
    function (
        $,
        _,
        __,
        BaseForm,
        template,
        DateFormatter,
        DateContext
    ) {
        return BaseForm.extend({
            template: _.template(template),
            dueDate: null,
            completeness: null,

            /**
             * {@inheritdoc}
             */
            configure (gridAlias) {
                this.gridAlias = gridAlias;

                if (_.has(__moduleConfig, 'forwarded-events')) {
                    this.forwardMediatorEvents(__moduleConfig['forwarded-events']);
                }

                this.listenTo(this.getRoot(), 'grid:view-selector:project-selected', this.updateProject.bind(this));
            },

            /**
             *
             * @param {Object} project
             * @param {String} project.due_date
             * @param {float} project.completeness.ratio_done
             */
            updateProject(project) {
                this.dueDate = project.due_date;
                this.completeness = Math.round(project.completeness.ratio_done);

                this.render();
            },

            /**
             * {@inheritdoc}
             */
            render() {
                this.$el.html('');

                if (null !== this.completeness && null !== this.dueDate) {
                    this.$el.append(this.template({
                        dueDate: DateFormatter.format(this.dueDate, 'yyyy-MM-dd', DateContext.get('date').format),
                        completeness: this.completeness,
                        badgeClass: this.getBadgeClass(),
                        dueDateLabel: __('teamwork_assistant.project.due_date')
                    }));
                }
            },

            getBadgeClass() {
                if (this.completeness <= 0) {
                    return 'AknBadge--invalid';
                } else if (this.completeness >= 100) {
                    return 'AknBadge--success';
                }

                return 'AknBadge--warning';
            }
        });
    }
);
