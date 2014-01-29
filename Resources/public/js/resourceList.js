/**
 *  javascript file used for ajax loading resource lists in the resource list view
 *  REQUIES jQuery
 */

$(document).ready(function() {
    /**
     *  Add onClick listeners to the folder links that ajax loads the contents and places under the folder,
     *  or if the folder is already 'open' then hide the contents
     *  Also change the folder icon to open or close
     */
    $("ul.mesdResourceList").on("click", "a.mesdJasperFolderLink", function(e) {
        //Don't go to the actual location
        e.preventDefault();
        //Get a reference to the current anchor tag
        var el = $(this);
        //Check if the 'folder' is currently opened or closed
        if (el.data("isopen")) {
            //Grab the icon and change it to closed
            el.children("span." + openFolderIconClass).removeClass(openFolderIconClass).addClass(closedFolderIconClass);
            //Set the anchor's isopen to false
            el.data("isopen", false);
            //hide the child list
            el.parent().children("ul.mesdResourceList").hide();
        } else {
            //Grab the icon and change it to open
            el.children("span." + closedFolderIconClass).removeClass(closedFolderIconClass).addClass(openFolderIconClass);
            //Set the anchor's isopen to true
            el.data("isopen", true);
            //Check if the 'folder' has already been opened or not (if its alreadt loaded, we're just going to unhide it)
            if (el.data("hasopened")) {  //unhide
                //show the child list
                el.parent().children("ul.mesdResourceList").show();
            } else {  //load
                //Ajax load the contents from the href location
                $.get(el[0].href, function(data) {
                    el.parent().append(data);
                });
                //Set has opened to true
                el.data("hasopened", true);
            }
        }
    });
});