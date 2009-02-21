// Highlight selected item

$(document).ready(function() {
    if (location.hash != "") {
        $(location.hash).addClass("selectedentry");
    }

    $(".vimeoclip").each(function() {
        var id = this.id.substring(6); // trim off initial "vimeo-" bit
        var width = $(this).width();
        var height = $(this).height();
        $(this).flash({
            swf: "http://vimeo.com/moogaloop.swf?clip_id=" + id,
            width: width,
            height: height,
            params: {allowscriptaccess: true, allowfullscreen: true}
        });
    });
});
