(function($){
    class WDASS__Tools {
        set ( key, data ) {
            localStorage.setItem( key, data );
        }
        setString ( key, data ) {
            localStorage.setItem( key, this.stringify( data ) );
        }
        get ( key = '' ) {
            return localStorage.getItem( key );
        }
        remove ( key = '' ) {
            localStorage.removeItem( key );
        }
        parse ( json ) {
            return JSON.parse( json );
        }
        getParsed ( key = '' ) {
            return this.parse( this.get( key ) );
        }
        stringify ( json ) {
            return JSON.stringify( json );
        }
        log ( data ) {
            console.log( data );
        }
    }

    class WDASS__meta_box extends WDASS__Tools {
        constructor () {
            super();
            
            this.postID = $('#post_ID');
            
            this.parentInput = $('#wdass_parent_data');
            this.termCats = $('#wdass_term_cats');
            this.termTags = $('#wdass_term_tags');

            this.metaBox = $('#_wdass_event_data');

            this.catList = $('#wdass--category-list');
            this.tagList = $('#wdass--tag-list');

            this.mediaField = $( '.wdass__media-field' );
            this.menu = $( '.wdass__meta-menu' );
            this.licenseKeyRevokeButton  = $( '#wdass__revoke-license-key' );

            this.homeurl = window.location.protocol + "//" + window.location.hostname;

            this.events();
        }

        events () {

            this.metaBox.on( 'change', '.wdass__variations .wdass_field', 'variable', this.data.bind(this) );
            
            // Tab switcher
            this.metaBox.on( 'click', '.wdass__meta-menu', '', this.switchTab.bind(this) );

            // Data Handler
            this.metaBox.on( 'change', '.wdass__parent-input', 'parent', this.data.bind(this) );
            this.metaBox.on( 'change', '.wdass__variations .wdass_field', 'variable', this.data.bind(this) );

            this.metaBox.on( 'click', '.wdass__variation-title', '', this.accordion.bind( this ) );

            this.metaBox.on( 'click', 'input[type="checkbox"]', '', this.checkbox.bind( this )  );

            this.catList.on( 'change', 'input', 'product_cat', this.terms.bind( this ) );
            this.tagList.on( 'change', 'input', 'product_tag', this.terms.bind( this ) );
        }

        terms ( e ) {
            const postID = this.postID.val();
            const termId = $( e.target ).data( 'id' );

            let modifiedData = this.parse( this.parentInput.val() );

            modifiedData[ postID ] = postID in modifiedData ? modifiedData[ postID ] : {};

            let terms = modifiedData[ postID ][ 'terms' ];


            terms = terms == '404' ? { 'product_cat' : [], 'product_tag' : [] } : this.parse( terms );

            if ( $( e.target ).is(':checked') ) {
                terms[ e.data ].push( termId );
            } else {
                terms[ e.data ] = terms[ e.data ].filter(obj => obj !== termId);
            }

            // modifiedData[ postID ][ 'terms' ] = terms;

            modifiedData[ postID ][ 'terms' ] = this.stringify( terms );

            this.parentInput.val( this.stringify( modifiedData ) );
            this.log( this.parse( this.parentInput.val() )[ postID ][ 'terms' ] );
        }

        checkbox ( e ) {
            if ( $( e.target ).is(':checked') ) {
                $( e.target ).val( $( e.target ).data( 'on' ) );
            } else {
                $( e.target ).val( $( e.target ).data( 'off' ) );
            }
        }

        accordion ( e ) {
            let container = $( e.target ).closest( 'li' ).find( '.wdass__variation-container' );

            if ( container.hasClass( 'hide' ) ) {
                container.removeClass( 'hide' );
            } else {
                container.addClass( 'hide' );
            }
        }

        data ( e ) {
            let props = {};
            let pid;

            props.key = $( e.target ).attr( 'name' ).replace( 'wdass_', '' );
            props.input = '#wdass_parent_data';
            
            pid = e.data == 'parent' ? this.postID.val() : $( e.target ).closest('li').data('id');
            
            this.addData( pid, props, $( e.target ).val() );
        }

        switchTab ( e ) {
            const id = $( e.target ).attr('data-menu');
            this.menu.removeClass('active');
            $( e.target ).addClass('active');
            
            this.metaBox.find( '.wdass__meta-content' ).addClass( 'hide' );
            $( `[data-container="${id}"]` ).removeClass( 'hide' );
        }

        addData ( postID, props, value ) {
            // let dataInput = this.metaBox.find( props.input );
            let data = this.parse( this.parentInput.val() );

            data[ postID ] = postID in data ? data[ postID ] : {};
            data[ postID ][ props.key ] = value;

            this.parentInput.val( this.stringify( data ) );
            this.log( this.parse( this.parentInput.val() ) );
        }

        fieldToggle ( wrapper, input, value = [], field ) {
            this.metaBox.on('change', wrapper + " " + input, '', ( e ) => {
                if ( value.includes( this.metaBox.find( e.target ).val() ) ) {
                    $( e.target ).closest( wrapper ).find( field ).hide();
                } else {
                    $( e.target ).closest( wrapper ).find( field ).show();
                }
            });
        }

    }

    var wdametabox = new WDASS__meta_box ();

    wdametabox.fieldToggle(
        'div[data-container="inventory"]',
        '[name="wdass__manage_stock"]',
        ['no'],
        '.form-field:has([name="wdass__stock"])'
    );

    wdametabox.fieldToggle(
        '.wdass__variations>li',
        '[name="wdass__manage_stock"]',
        ['no'],
        '.form-field:has([name="wdass__stock"])'
    );
})(jQuery)