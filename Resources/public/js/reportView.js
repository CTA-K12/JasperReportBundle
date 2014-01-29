/*
 *  javascript file for ajax handling in the views used by the report controller
 *  REQUIES jQuery
 */

$( document ).ready(function() {
    /**
     *  When a folder link is pressed, if the list is closed open it via ajax, else close it
     */
    $(".mesdJasperFolderLink").onClick(function(e) {
        e.preventDefault();
    });
});