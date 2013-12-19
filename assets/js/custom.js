function wrapText(elementID, openTag, closeTag) {
    var textArea = $('#' + elementID);
    var len = textArea.val().length;
    var start = textArea[0].selectionStart;
    var end = textArea[0].selectionEnd;
    var selectedText = textArea.val().substring(start, end);
    var replacement = openTag + selectedText + closeTag;
    textArea.val(textArea.val().substring(0, start) + replacement + textArea.val().substring(end, len));
}



$(document).ready(function() {

    $("#menu-toggle").click(function(e) {
        e.preventDefault();
        $("#wrapper").toggleClass("active");
        $("#menu-toggle").children('i').toggleClass("glyphicon-align-justify");
        $("#menu-toggle").toggleClass("active");
        $("#menu-toggle").children('i').toggleClass("glyphicon-remove");
    });

    // Unhide some things when their content changes
    /* Settings.php */
    $('input[name="user-email"]').focus(function(event){
        $("#emailWarning").show();
    });

    $('input[name="user-password"]').focus(function(event){
        $("#passwordWarning").show();
    });
    /* Settings.php */

    /* User */
    $('.iHide').hide(); // want to hide it myself
    // Otherwise lets check for changes and then post them through
    $('form.users_update_group').change(function(event) {
       $(this).submit();
    });
    $('form.users_update_email').change(function(event) {
       $(this).submit();
    });
    $('form.users_update_password').change(function(event) {
       $(this).submit();
    });
    $('form.users_update_activation').change(function(event) {
       $(this).submit();
    });
    /* User */


    /* BLOG */
    // This is going to be used wherever I want to edit a comment or something by double clicking it.
    // You will double-click the div and it will show the form and hide the div
    $(".editDiv").dblclick(function(event) {
        event.preventDefault();
        $(this).next("form['name=editForm']").show();
        $(this).next(".editForm").show();
        $(this).hide();
    });


    $(".editLink").click(function(event) {
        event.preventDefault();
        $(this).next(".editForm").show();
    });


    $('.bbcode').click(function(event){
        event.preventDefault();
        var pre = $(this).children('.pre').html();
        var post = $(this).children('.post').html();
        wrapText('text', pre, post);
    });


    $('input[id=lefile]').change(function() {
       $('#fileName').val($(this).val());
       alert("Clicked");
    });


    $('.insertImage').click(function(event) {
        event.preventDefault();
        var imageSource = $(this).attr('src');
        var largeImage = imageSource.replace("thumbs/", '');
        var getText = $("#text").val();
        $("#text").val(getText + " [img]" + largeImage + "[/img]");
    });


    // Blog Remove image
    $('.removeBlogImage').click(function(event) {
        event.preventDefault();
        var getSrc = $(this).attr('data-src');
        $.post("blog_edit.php", { action: "unlink", img: getSrc }, function( data ) {


            if (data == "true")
            {
                var getText = $("#text").val();
                var newText = getText.replace("[img]"+getSrc+"[/img]", '');
                $('#text').val(newText);
            }

            else
            {
                alert("Weird, unable to delete the image");
            }
        });
        $(this).parent("div").remove();
    });


	$("#container").attr("data-value", $("#container").width());

    // Hook into the confirm plugin for general actions
    $(".confirm").confirm({
        text: "Are you sure you want to continue?"
    });

    $('input[type=file]').bootstrapFileInput();
    $('.file-inputs').bootstrapFileInput();

    // Hook into the confirm plugin for deletions
    $(".confirmDelete").confirm({
        text: "Are you sure you want to continue deleting this entry?",
        post: true
    });


    // Hook into the confirm plugin for untracking
    $(".confirmUntrack").confirm({
        text: "Are you sure you want to no longer track this show?"
    });


    /* BLOG */
    $('.thumbnailOverlay').hide();
    $('.thumbnail').hover(function(){
        $(this).children('.thumbnailOverlay').fadeTo(300, 0.8);
        event.preventDefault();
    },function(){
        $(this).children('.thumbnailOverlay').fadeTo(300, 0.0).hide();
    });


    /* GENERAL */
    // Focus on the default field by adding the focus class to it
    $('.focus').focus();
    $('.hideMe').hide();
    /* BLOG */
    $('.thumbnailOverlay').hide();
    $('.thumbnail').hover(function(){
        $(this).children('.thumbnailOverlay').fadeTo(300, 0.8);
        event.preventDefault();
    },function(){
        $(this).children('.thumbnailOverlay').fadeTo(300, 0.0).hide();
    });


    /* GENERAL */
    // Focus on the default field by adding the focus class to it
    $('.focus').focus();
    $('.hideMe').hide();

});


// This function will set the scrolling so that when a page refreshes to the same screen that your offet is the same
function page_scroller()
{
    var pageOffset = $(document).scrollTop();
    set_cookie("scroll", pageOffset);
}

if (pageOffsetCookie != 'null')
{
    $(document).scrollTop(pageOffsetCookie);
}

$(window).scroll(function(event){
    page_scroller()
});


/* GENERAL */