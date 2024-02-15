(function( $ ) {
	'use strict';


    setTimeout(function () {    
        $('#kiwop_prisma_etiquetes').after('<div id="CercadorDeTagsPrisma" ><div id="PrismaTagSelectorWrapper"></div><div id="PrismaSelectedTags"></div></div>');        
        $('#kiwop_prisma_etiquetes').attr('autocomplete', 'off'  );
    }, 1000);


    var debounceTimer;
    var actual_tags = [];

    function performSearch(query) {  
        
        let img = ' <img class="loader" src="'+kiwop_aportacions_recursos_globals.loader+'" />';
        $('#PrismaTagSelectorWrapper').html(img);

        $.ajax({
            url: kiwop_aportacions_recursos_globals.ajax_url,
            data: { 
                search: query,
                action: 'kiwopPrismaSearchTags',
                _ajax_nonce: kiwop_aportacions_recursos_globals.kiwopPrismaSearchTags
            },
            type: 'POST',
            dataType : 'json',
            success: function(response) {
                if (response.data.tags.length > 0) {
                    let html = '';
                    response.data.tags.forEach(function (tag) {
                        html += '<button type="button" class="kiwop-prisma-result-search-tag" value="' + tag.term_id + '">' + tag.tag + '</button> &nbsp;&nbsp;&nbsp; ';                        
                    });
                    $('#PrismaTagSelectorWrapper').html(html);
                } else {
                    $('#PrismaTagSelectorWrapper').html('No s`han trobat etiquetes');
                }
            },
            error: function(errorThrown){
                //console.error('Error en la consulta AJAX', errorThrown);
                $('#PrismaTagSelectorWrapper').html(errorThrown);
            }
        });

    }
  
    function addTagToSelectedTags(term_id, tag)
    {
        if (actual_tags.length >= 10 ) {
            if ( $('#PrismaSelectedTags spam.prismaAlert').length === 0) { 
                $('#PrismaSelectedTags').prepend('<spam class="prismaAlert" >No es poden seleccionar més de 10 etiquetes</span> <br />');
            }
            return false;
        }

        if ( $('#PrismaSelectedTags spam.prismaAlert').length > 0) { 
            $('#PrismaSelectedTags spam.prismaAlert').remove();
        }

        $('#PrismaSelectedTags spam.alert').remove();

        var indexToFind = actual_tags.findIndex(function(objeto) {
            if (term_id > 0) {
                return objeto['id'] === term_id;
            } else {
                return objeto['tag'] === tag;
            }
        });
        
        if (indexToFind === -1)  {
            actual_tags.push({'id':term_id,'tag':tag});            
            
            updateHiddenTagsFieldValue();

            if ($('#PrismaSelectedTags').html() === '') {
                $('#PrismaSelectedTags').append('<h5>Etiquetes seleccionades</h5>');
            }
            $('#PrismaSelectedTags').append('<button type="button" class="kiwop-prisma-tag-to-save" value="' + term_id + '">' + tag + '</button>');

            return true;
        }
        return false;
    }

    // Manejar cambios en el campo de búsqueda
    $(document).on('click','.kiwop-prisma-result-search-tag', function () {
        let term_id = $(this).val();
        let tag = $(this).html();

        addTagToSelectedTags(term_id, tag);        
    });

    $(document).on('input','#kiwop_prisma_etiquetes', function (e) {
        
        e.preventDefault();

        var currentQuery = $(this).val();
    
        // Cancelar el temporizador anterior si existe
        clearTimeout(debounceTimer);
    
        // Configurar un nuevo temporizador
        if (currentQuery.length > 1) {
            debounceTimer = setTimeout(function () {
                // Realizar la búsqueda solo si ha pasado un segundo desde la última acción
                performSearch(currentQuery);
            }, 500);
        } else {
            $('#PrismaTagSelectorWrapper').html('Per cercar introduïu almenys 2 caràcters');
        }

    });
  
    // Manejar pulsaciones de teclas en el campo de búsqueda
    $(document).on('keyup','#kiwop_prisma_etiquetes', function (e) {
        e.preventDefault();

        let val = $(this).val();

        if (e.keyCode === 13 && val.length > 2) {
            // Si se pulsa "Intro", agregar el valor al área de tags seleccionados
            var selectedTag = $('#kiwop_prisma_etiquetes').val();
            
            if ( addTagToSelectedTags(null, selectedTag) ) {            
                $('#kiwop_prisma_etiquetes').val(''); // Limpiar el campo de búsqueda
            }
        }

        if (val === '') {
            $('#PrismaTagSelectorWrapper').html('');            
        }
    });

    document.addEventListener( 'wpcf7mailsent', function( event ) {
        $('#PrismaTagSelectorWrapper').html('');
        $('#PrismaSelectedTags').html('');
        $('.dscf7_captcha_icon').trigger('click');
    }, false );

    $(document).on('click', '.kiwop-prisma-tag-to-save', function () {
        
        if ( $('#PrismaSelectedTags spam.prismaAlert').length > 0) { 
            $('#PrismaSelectedTags spam.prismaAlert').remove();
        }

        let tag = $(this).html();
        let term_id = $(this).val();       

        var indiceAEliminar = actual_tags.findIndex(function(objeto) {
            if (term_id > 0) {
                return objeto['id'] === term_id;
            } else {
                return objeto['tag'] === tag;
            }
        });
        
        if (indiceAEliminar !== -1)  {
            updateHiddenTagsFieldValue();
            
            // Elimina el objeto del array en el índice encontrado
            actual_tags.splice(indiceAEliminar, 1);
        }        

        if (actual_tags.length == 0) {
            $('#PrismaSelectedTags').html('');
        }

        $(this).remove();
        

    });

    function updateHiddenTagsFieldValue() {
        let tags_b64 = btoa(JSON.stringify(actual_tags));
        console.log("actual_tags",actual_tags.length);
        $('#kiwop_prisma_etiquetes_hidden').val(tags_b64);
    }

})( jQuery );
