$(function(){
    $('#garder').click(function(){
        var  url = Routing.generate('template_garder');
        $('#corrections').attr('action',url);
        $('#corrections').submit();
    });

    $('#valider_modifs').click(function () {
        var  url = Routing.generate('template_corriger');
        $('#corrections').attr('action',url);
        $('#corrections').submit();
    });

    $('#copie').click(function(){
        $('#form_copie').submit();
        //on affiche un Spinner de chargement empeche second click
        $("#mySpinnerlabel").text("Recopie en cours...");
        $("#mySpinnerModal").modal("show");
    });

    $('#saison_source').on('change',function(){
        if ( (typeof eval($('#copie').attr('currentTemplateid')) != "undefined") && (typeof eval($('select#saison_cible option:selected').val()) != "undefined") && (typeof eval($('select#saison_source option:selected').val()) != "undefined") && (typeof eval($('select#template_cible option:selected').val()) != "undefined") ){
            $('#copie').removeAttr('disabled');
        }else{
            $('#copie').attr('disabled','disabled');
        }
        /*if( eval( $('#duplication').attr('templatesourceid') ) != 1 ){//si le programme reel n'est pas le template source
            if( eval( $('select#saison_source option:selected').val() ) == eval( $('select#saison_cible option:selected').val() ) ){//si la saison cible est égale à la saison source
                $('#duplication').removeAttr('disabled');
            }else{
                $('#duplication').attr('disabled','disabled');
                $('#duplication').attr('disabled','disabled');
            }
        }*/
    });

    $('#saison_cible').on('change',function(){
        if ( (typeof eval($('#copie').attr('currentTemplateid')) != "undefined") && (typeof eval($('select#saison_cible option:selected').val()) != "undefined") && (typeof eval($('select#saison_source option:selected').val()) != "undefined") && (typeof eval($('select#template_cible option:selected').val()) != "undefined") ){
            $('#copie').removeAttr('disabled');
        }else{
            $('#copie').attr('disabled','disabled');
        }
        /*if( eval( $('#duplication').attr('templatesourceid') ) != 1 ){//si le programme reel n'est pas le template source
            if( eval( $('select#saison_source option:selected').val() ) == eval( $('select#saison_cible option:selected').val() ) ){//si la saison cible est égale à la saison source
                $('#duplication').removeAttr('disabled');
            }else{
                $('#duplication').attr('disabled','disabled');
            }
        }*/
    });

    $('#template_cible').on('change',function(){
        if ( (typeof eval($('#copie').attr('currentTemplateid')) != "undefined") && (typeof eval($('select#saison_cible option:selected').val()) != "undefined") && (typeof eval($('select#saison_source option:selected').val()) != "undefined") && (typeof eval($('select#template_cible option:selected').val()) != "undefined") ){
            $('#copie').removeAttr('disabled');
        }else{
            $('#copie').attr('disabled','disabled');
        }
    });

    //$('#duplication').click(function(){
    //    $('#duplicationTemplateAction').val('1');
    //    $('#form_copie').submit();
    //});

});
