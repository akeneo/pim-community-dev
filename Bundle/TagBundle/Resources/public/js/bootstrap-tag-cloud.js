$(document).on('click','.tag-cloud', function removeTag() {
    $(this).remove();
});

Oro.Events.bind(
    "hash_navigation_request:complete",
    function () {
        addTagBindings('.tag-cloud-field');
    }
);

if (!Oro.hashNavigationEnabled()) {
    $(document).ready(function()
    {
        addTagBindings('.tag-cloud-field');
    });
}

function addTagBindings(selector)
{
    $(selector).each(function(){
        var tagRow = $(this);

        tagRow.find('button').click(function(){ addTag(tagRow); });
        tagRow.find('input').keyup(function (e) {
            if (e.keyCode == 13) {
                addTag(tagRow);
            }
        });
    });
}

function addTag(tagRow)
{
    var tagInput = tagRow.find('input');
    var Tag = tagInput.val();
    var id = tagRow.attr('id');

    if (Tag != '') {
        $('<li class="tag-cloud' + (id ? ' tag-cloud-' + id : '') + '">'+Tag+'</li>').appendTo(tagRow.find("#tag-cloud"));
        tagInput.val('');
    }
}