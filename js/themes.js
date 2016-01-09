jQuery( function($){
    $('.siteorigin-themes .theme .siteorigin-demo').click( function( e ){
        e.preventDefault();

        var $$ = $(this),
            $theme = $$.closest('.theme'),
            $modal = $('.demo-modal');

        // Show the modal loading
        $modal.show().addClass('loading');
        $modal.find('.demo-sidebar, .demo-iframe').hide();

        $.getJSON(
            $('.demo-modal .theme-info').data('info-url'),
            {
                'theme_slug' : $theme.data('slug'),
                'theme_version' : $theme.data('version')
            },
            function(data){
                $modal.find('.theme-name').html( data.name );
                $modal.find('.theme-author').html( data.author );
                $modal.find('.theme-description').html( data.description );
                $modal.find('.theme-screenshot img').attr( 'src', $theme.find('.screenshot').data('screenshot') );
                $modal.find( '.action-buttons').empty().append(
                    $theme.find('.buttons').clone()
                );
                $modal.find( '.action-buttons .siteorigin-demo').remove();

                $modal.find('.demo-iframe iframe').attr( 'src', $$.attr( 'href' )).hide().load( function(){
                    $modal.find('.demo-iframe').removeClass('loading');
                    $(this).show();
                } );
                $modal.find('.demo-iframe').addClass('loading');

                $modal.removeClass('loading');
                $modal.find('.demo-sidebar, .demo-iframe').show();
            }
        );
    } );

    $('.demo-modal .top-toolbar .close').click( function(){
        $('.demo-modal').hide();
    } );
} );