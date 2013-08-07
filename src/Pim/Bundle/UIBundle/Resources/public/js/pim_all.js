$(function() {
    Oro.Navigation.prototype.bind('route', init);
});

function init() {
    // Place code that we need to run on every page load here
}

$(function() {
    // Do global event binding here

    // Toogle accordion icon
    $(document).on('show hide', '.accordion', function(e) {
        $(e.target).siblings('.accordion-heading').find('.accordion-toggle i').toggleClass('fa-icon-collapse-alt fa-icon-expand-alt');
    });
});
