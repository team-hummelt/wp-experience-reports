jQuery(function ($) {

    let container = $( '#container' );
    container.html( '<ul class="filetree start"><li class="wait">' + 'Generating Tree...' + '<li></ul>' );
   // let folder = $('#reportPluginRoot').attr('data-folder');

/* getfilelist( container , folder );*/
 function getfilelist( cont, root ) {
     let folder = $('#reportPluginRoot').attr('data-folder');
     $( cont ).addClass( 'wait' );
     $.post( report_ajax_obj.ajax_url,
         {
             'action': 'ERFolderThreeHandle',
             '_ajax_nonce': report_ajax_obj.nonce,
             dir: root
         }, function( data ) {
         $( cont ).find( '.start' ).html( '' );
         $( cont ).removeClass( 'wait' ).append( data );
         if( folder == root )
             $( cont ).find('UL:hidden').show();
         else
             $( cont ).find('UL:hidden').slideDown({ duration: 500, easing: null });
     });
 }

 container.on('click', 'LI A', function() {
     let entry = $(this).parent();
     $(this).trigger('blur');
     if( entry.hasClass('folder') ) {
         if( entry.hasClass('collapsed') ) {
             entry.find('UL').remove();
             getfilelist( entry, escape( $(this).attr('rel') ));
             entry.removeClass('collapsed').addClass('expanded');
         }
         else {
             entry.find('UL').slideUp({ duration: 500, easing: null });
             entry.removeClass('expanded').addClass('collapsed');
         }
        let selectFolder = $(this).attr('data-folder');
        let currentSelect = $( '#container li a');
        currentSelect.removeClass('active');
        $(this).addClass('active');
         $('.btn-select-folder').attr('data-source', selectFolder);
        let html = `<i class="bi bi-folder2-open text-muted"></i>&nbsp;
                     <b class="strong-font-weight wp-blue">${selectFolder}</b>`;
         $('.ordner-select').html(html)

     } else {
         const regex = /.*?-.+\d\/|(.+)/gm;
         let text = $(this).attr( 'rel' );
         let m;
         while ((m = regex.exec(text)) !== null) {
             // This is necessary to avoid infinite loops with zero-width matches
             if (m.index === regex.lastIndex) {
                 regex.lastIndex++;
             }
             // The result can be accessed through the `m`-variable.
             m.forEach((match, groupIndex) => {
                 //console.log(`Found match, group ${groupIndex}: ${match}`);
                 if(groupIndex === 1) {
                   $( '#selected_file' ).text(match);
                     return false;
                 }
             });
         }
        $( '#selected_file' ).text( "File:  " + $(this).attr( 'rel' ));
     }
     return false;
 });


 function api_xhr_rest_admin_endpoint_data(data,uri,callback) {
     let xhr = new XMLHttpRequest();
     let formData = new FormData();
     for (let [name, value] of Object.entries(data)) {
         formData.append(name, value);
     }
     xhr.open('POST', ERRestObj.api_url + uri, true);
     xhr.onreadystatechange = function () {
         if (this.readyState === 4 && this.status === 200) {
             if (typeof callback === 'function') {
                 xhr.addEventListener("load", callback);
                 return false;
             }
         }
     }
    // xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
     //xhr.setRequestHeader('Content-Type', 'application/json;charset=UTF-8')
     xhr.setRequestHeader('X-WP-Nonce', ERRestObj.nonce);
     xhr.send(formData);
 }

 $(document).on('click', '.btn-show-file', function (e) {
     let folder = $(this).attr('data-folder');
     let file = $(this).attr('data-file');
     let formData = {
         'file' :file,
         'folder':folder,
         'method':'get-twig-file'
     }

     api_xhr_rest_admin_endpoint_data(formData,'wp-report-posts', show_twig_file_callback)
 });



 function show_twig_file_callback() {
     let data = JSON.parse(this.responseText);
     let threeCollapse = document.getElementById('collapseFolderThreeEdit');
     let showFileContainer = document.getElementById('showTwigFile');
     showFileContainer.innerHTML=data.file;
     let bsCollapse = new bootstrap.Collapse(threeCollapse, {
         toggle: true,
         parent:'#post_display_parent'
     });
 }


 function loadFolderThree(){
     let container = $( '#container' );
     container.html( '<ul class="filetree start"><li class="wait">' + 'Generating Tree...' + '<li></ul>' );
     let folder = $('#reportPluginRoot').attr('data-folder');
     getfilelist( container , folder );
 }
    $(document).on('click', '#loadViewFileThree', function () {
        loadFolderThree();
    })

    $(document).on('dblclick', '#extra-option-settings', function () {
        $('#btnExtraOption').removeClass('d-none');
    })


 //folder
 $(document).on('click', '.btn-show-folder-tree', function () {
     $('.show-form-input').toggleClass('d-none');
     let btnTarget = $(this).attr('data-target');
     if(btnTarget) {
        let btnSelectFolder = $('.btn-select-folder');
        btnSelectFolder.attr('data-target',btnTarget);
     }
 });
});

