$(document).ready(function(){  
    // set up the click event
    $('ul.navMenu li a').click(function() {
        var li_id = $(this).attr('href');
        var toLoad = 'index.php?request=html&page=' + escape(li_id.replace(/navMenu\-/, ''));
        $('#content').slideUp('fast', loadContent);
        $('#contentload').fadeIn('fast');
        function loadContent() {
            $('#box').load(toLoad, '', function(response, status, xhr) {
                if (status == 'error') {
                    var msg = "Sorry but there was an error: ";
                    $("#box").html(msg + xhr.status + " " + xhr.statusText);
                }            
            });
            $('#content').delay(800).slideDown('fast', hideLoader());
        }
        function hideLoader() {
            $('#contentload').delay(1000).fadeOut('fast');
        }
        return false;
    });
});