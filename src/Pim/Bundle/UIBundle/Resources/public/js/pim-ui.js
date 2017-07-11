import $ from 'jquery'
import initSelect2 from 'pim/initselect2'
import wysiwyg from 'wysiwyg'
import 'bootstrap'
import 'bootstrap.bootstrapswitch'

export default function ($target) {
            // Apply Select2
  initSelect2.init($target)

            // Apply bootstrapSwitch
  $target.find('.switch:not(.has-switch)').bootstrapSwitch()

            // Initialize tooltip
  $target.find('[data-toggle="tooltip"]').tooltip()

            // Initialize popover
  $target.find('[data-toggle="popover"]').popover()

            // Activate a form tab
  $target.find('li.tab.active a').each(function () {
    var paneId = $(this).attr('href')
    $(paneId).addClass('active')
  })

  $target.find('textarea.wysiwyg[id]:not([aria-hidden])').each(function () {
    if (!$(this).closest('.attribute-field').hasClass('scopable')) {
      wysiwyg.init($(this))
    }
  })
};
