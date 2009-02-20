// Highlight selected item

$(document).ready(function() {
    if (location.hash != "") {
        $(location.hash).addClass("selectedentry");
    }
});
