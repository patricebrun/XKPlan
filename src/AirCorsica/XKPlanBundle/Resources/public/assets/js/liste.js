//detection de la fin du chargement de la liste
//document.onreadystatechange = function () {
//    if (document.readyState == "complete") {
//        alert('chargement terminé!');
//    }
//}

$(function(){

    $('#aller').click(function(){
        if(!$(this).is(':checked')){
            if(!$('#retour').is(':checked')) {
                $('#retour').click();
            }
        }
    })

    $('#retour').click(function(){
        if(!$('#aller').is(':checked')){
            $('#aller').click();
            // $(this).attr('checked',false);
        }
    })

    $('#purger').click(function () {
        bootbox.confirm(
            {
                message :   "<p><i class='glyphicon glyphicon-alert info'></i> Désirez-vous vraiment purger le template sélectionné  de son sontenu.</p>" +
                            "<p>Cette opération est irréversible et aura pour conséquence de détruire tous les vols, messages SITA expédiés ou non, afin de repartir sur une base vierge.</p>" +
                            "<p>Désirez-vous continuer ?</p>",

                buttons: {
                    'cancel': {
                        label: 'Non',
                        className: 'btn-default pull-left'
                    },
                    'confirm': {
                        label: 'Oui',
                        className: 'btn-danger pull-right'
                    }
                },
                callback: function(result) {
                    if(result){
                        var idTemplate = $('#template').val();
                        var url = Routing.generate('template_purge', { id : idTemplate});
                        window.location.replace(url);
                    }
                }
            }

        );
    });

    $("#select2-template-container .select2-selection__clear").remove();

    $('#template').change(function (e) {
        if($(this).val()) {
            var id = $(this).val();
            var url = Routing.generate('template_switch', {id: id});
            window.location.replace(url);
        }
    });

    $('#etat_jours').click(function(){
        if($(this).prop('checked') == true){
            $('.checkbox-jour-semaine').attr("disabled", false);
        }else{
            $('.checkbox-jour-semaine').prop('checked',false);
            $('.checkbox-jour-semaine').attr("disabled", true);
        }
    });

    $('#appliquer').click(function (e) {
        e.preventDefault();
        var ids = new Array();
        $.each( $('.id_vol:checked'),function (e) {
            ids.push($(this).val());
        });

        if(0 == ids.length){
            bootbox.alert("<i class='glyphicon glyphicon-alert' style='color: red;'></i> Vous devez cocher au minimum une ligne de vols pour lui appliquer des modifications ponctuelles.");
            return false;
        }else{
            bootbox.confirm(
                {
                    message :   "<p><i class='glyphicon glyphicon-alert' style='color: red;'></i> ATTENTION : Vous allez modifier les lignes de vols cochés par l'application ponctuelle de changements.<br /><br /> Désirez-vous continuer?</p>",

                    buttons: {
                        'cancel': {
                            label: 'Non',
                            className: 'btn-default pull-left'
                        },
                        'confirm': {
                            label: 'Oui',
                            className: 'btn-danger pull-right'
                        }
                    },
                    callback: function(result) {
                        if(result){
                            $('#type_vol_val').val($('#type_vol').val());
                            $('#nature_vol_val').val($('#nature_vol').val());
                            $('#debut_filtre').val($('#date_debut').val());
                            $('#fin_filtre').val($('#date_fin').val());

                            var jour = new Array();
                            $.each($('.checkbox-jour-semaine'),function (i) {
                                if($(this).is(':checked')){
                                    jour[i] = eval(i+1);
                                }else{
                                    jour[i] = '-';
                                }
                            });
                            $('#jour_val').val(jour.join('_'));
                            $('.ids_vol').val(ids.join('_'));
                            $('#form-ponc').submit();
                        }
                    }
                }

            );

            // bootbox.alert("<i class='glyphicon glyphicon-alert' style='color: red;'></i> ATTENTION : Vous allez modifier les lignes de vols cochés par l'application ponctuelle de changements.<br /><br /> Désirez-vous continuer?");
            // return false;
        }
    });

    $('#tout_cocher_vols').click(function() {
        var c = this.checked;
        $(':checkbox.id_vol:not(".deleste")').prop('checked',c);
    });

    /*$('#check-all').click(function (e) {
        var checkBoxes = $(".id_vol");
        checkBoxes.prop("checked", !checkBoxes.prop("checked"));
    });*/

    $('#modif').click(function(){
        if($(this).prop('checked') == true){
            $('#date_debut_modif').attr("disabled", false);
            $('#date_fin_modif').attr("disabled", false);
        }else{
            $('#date_debut_modif').attr("disabled", true);
            $('#date_fin_modif').attr("disabled", true);
        }
    });

    $('#modif_date_deb').click(function(){
        if($(this).prop('checked') == true){
            $('#date_debut').attr("disabled", false);
        }else{
            $('#date_debut').attr("disabled", true);
        }
    });

    $('#modif_date_fin').click(function(){
        if($(this).prop('checked') == true){
            $('#date_fin').attr("disabled", false);
        }else{
            $('#date_fin').attr("disabled", true);
        }
    });

    $('#appliquer_coherence').click(function (e) {
        e.preventDefault();

        var ids = new Array();
        $.each( $('.id_vol:checked'),function (e) {
            ids.push($(this).val());
        });

        if(0 == ids.length){
            bootbox.alert("<i class='glyphicon glyphicon-alert' style='color: red;'></i> Vous devez cocher au minimum une ligne de vols.");
            return false;
        }

        $('.ids_vol').val(ids.join('_'));

        $('#form-coherence-vol').submit();
    });

    $('#appliquer_mess').click(function (e) {
        e.preventDefault();
        if($('#ssim').is(':checked')){
            $('#myModal').modal('show', {backdrop: 'static', keyboard: false});
            $('#myModal .modal-body').html("<span>Veuillez patienter pendant la génération du SSIM. Cela peut pendre une dizaine de minutes.</span><div class='progress' id='loadingDiv'><div class='progress-bar progress-bar-striped progress-bar-success active' role='progressbar' \n\
                                 aria-valuenow='100' aria-valuemin='0' aria-valuemax='100' style='width: 100%'></div></div>");
        }

        if($('#amos').is(':checked')){
            $('#myModal').modal('show', {backdrop: 'static', keyboard: false});
            $('#myModal .modal-body').html("<span>Veuillez patienter pendant la génération du fichier AMOS. Cela peut pendre une dizaine de minutes.</span><div class='progress' id='loadingDiv'><div class='progress-bar progress-bar-striped progress-bar-success active' role='progressbar' \n\
                                 aria-valuenow='100' aria-valuemin='0' aria-valuemax='100' style='width: 100%'></div></div>");
        }

        if($('#amos').is(':checked') && $('#ssim').is(':checked')){
            $('#myModal').modal('show', {backdrop: 'static', keyboard: false});
            $('#myModal .modal-body').html("<span>Veuillez patienter pendant la génération des fichiers AMOS et SSIM. Cela peut pendre une dizaine de minutes.</span><div class='progress' id='loadingDiv'><div class='progress-bar progress-bar-striped progress-bar-success active' role='progressbar' \n\
                                 aria-valuenow='100' aria-valuemin='0' aria-valuemax='100' style='width: 100%'></div></div>");
        }

        if( $('input[name=asm_ssm]').is(':checked') || $('input[name=scr]').is(':checked') ){

            //var ids = new Array();
            var idsString ='';
            $.each( $('.id_vol:checked'),function (e) {
                //on ne transmet a la messagerie que les vol avec l'icone fa-ban
                if($(this).parent().parent().next().children().find("i").length == 2){
                    //ids.push($(this).val());
                    idsString += $(this).val()+'_';
                }

            });

            //on enleve le dernier "_"
            idsString = idsString.slice(0, -1);
            $("#arrayIdVolsPourGenererSCRASMSSM").val(idsString);

            if('' == idsString){
                bootbox.alert("<i class='glyphicon glyphicon-alert' style='color: red;'></i> Vous devez cocher au minimum une ligne de vols.");
                return false;
            }

        }

        $('#form-message').submit();

    })

    $('#verificateur_periode').click(function () {
        $('#myModal').modal('toggle');
        $('#myModal .modal-body').html("<span>Veuillez patienter pendant la vérification des périodes. Cela peut pendre quelques minutes.</span><div class='progress' id='loadingDiv'><div class='progress-bar progress-bar-striped progress-bar-success active' role='progressbar' \n\
                             aria-valuenow='100' aria-valuemin='0' aria-valuemax='100' style='width: 100%'></div></div>");

    })


    /*$('#verificateur_periode').click(function (e) {
        e.preventDefault();

        $('#date_debut_val').val($('#date_debut').val());
        $('#date_fin_val').val($('#date_fin').val());

        var date_debut_val = $('#date_debut_val');
        var date_fin_val = $('#date_fin_val');

        if(!date_debut_val && !date_fin_val){
            bootbox.confirm("<p><i class='glyphicon glyphicon-alert info'></i> Vous n\'avez pas indiqué de période de date dans les filtres modificateurs.</p>" +
                "<p>Toutes les données de la DATABASE vont être traitées.</p>" +
                "<p>Désirez-vous continuer?</p>"
            );

            $('#date_debut_val').empty();
            $('#date_fin_val').empty();

            $('#form-verificateur-periode').submit();
        }else if(!date_debut_val || !date_fin_val){
            bootbox.alert("<p><i class='glyphicon glyphicon-alert danger'></i> Vous devez saisir une date de début et une date de fin dans les filtres de sélection modificateurs pour borner votre vérification.</p>");
            return false;
        }
    });*/


    //$('#appliquer_mess').click(function (e) {
    //    e.preventDefault();
    //
    //    var ids = new Array();
    //    $.each( $('.id_vol:checked'),function (e) {
    //        //on ne transmet a la messagerie que les vol avec l'icone fa-ban
    //        if($(this).parent().next().children().find("i").length == 2){
    //            ids.push($(this).val());
    //        }
    //
    //    });
    //
    //    if(0 == ids.length){
    //        bootbox.alert("<i class='glyphicon glyphicon-alert' style='color: red;'></i> Vous devez cocher au minimum une ligne de vols.");
    //        return false;
    //    }
    //
    //    var generer_asmssm;
    //    var generer_scr;
    //    var generer_ssim;
    //
    //    if( $('input[name=asm_ssm]').is(':checked') ){generer_asmssm = true;}else{generer_asmssm = false;}
    //    if( $('input[name=scr]').is(':checked') ){generer_scr = true;}else{generer_scr = false;}
    //    if( $('input[name=ssim]').is(':checked') ){generer_ssim = true;}else{generer_ssim = false;}
    //
    //    window.location.href = Routing.generate('vol_messagerie',{aId: ids, generer_asmssm: generer_asmssm, generer_scr: generer_scr, generer_ssim: generer_ssim})
    //
    //});


});
