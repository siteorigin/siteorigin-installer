jQuery( function($){

    $('.siteorigin-themes .theme').each( function(){
        var $$ = $(this),
            slug = $$.data('slug');

        //$.get('https://themes.svn.wordpress.org/' + slug + '/', function(data){
        //} );

        // Start by attempting to load the image from standard WordPress.org
        var img = new Image();
        img.onload = function(){
            console.log('GOOD IMAGE');
        };
        img.onerror = function(){
            console.log('BAD IMAGE');
        };
        img.src = '//ts.w.org/wp-content/themes/' + slug + '/screenshot.png';
    } );

} );