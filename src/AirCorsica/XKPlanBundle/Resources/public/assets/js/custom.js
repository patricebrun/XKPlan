'use strict';
//  Author: TemplateMonster.com
//
//  This file is reserved for changes made by the use.
//  Always seperate your work from the theme. It makes
//  modifications, and future theme updates much easier
//

function bindSubmitFormVol(){
    $('body').delegate("#form_vol_edit","submit",function(e){
        var $btn = $(document.activeElement);
        //si on clique su enregister fait un submit normal
        //sinon on enregistre en ajax et on recharge la popup
        if("valider" == $btn.attr("name")){
            return true;
        }

        e.preventDefault();
        var formSerialize = $(this).serialize() + "&poursuivre=1";
        var url  = Routing.generate('vol_new');

        $.post(url, formSerialize, function(response){

            $.ajax({
                type: "GET",
                url: Routing.generate('vol_modal_new',{volsuivant:response['volsuivant'],periode_2:response['periode_2'],dateDebut2:response['dateDebut2'],dateFin2:response['dateFin2']}),
                success: function (data) {
                    $('#myModal .modal-body').html(data);

                    //dans le cas ou l'on se trouve dans un modal enregistrer et poursuivre
                    //au moin un vol a été enregistré et donc en cas de fermeture du modal
                    //on recharge la page
                    $('#myModal .close').unbind('click');
                    $('#myModal .close').on('click', function () {
                        var urltotest = window.location.pathname;
                        if (urltotest.indexOf('planningvol') !== -1) {
                            window.location.reload();
                        }
                    });

                    bindSubmitFormVol();
                },
                complete: function () {
                    $('#form_vol_edit .dropdown-toggle').dropdown();
                    //$('#aircorsica_xkplanbundle_vol_aeroport_depart').select2('open');
                },
                beforeSend: function () {
                    $('#myModal').modal('toggle');
                    $('#myModal .modal-body').html("<div class='progress' id='loadingDiv'><div class='progress-bar progress-bar-striped progress-bar-success active' role='progressbar' \n\
                                         aria-valuenow='100' aria-valuemin='0' aria-valuemax='100' style='width: 100%'></div></div>");
                }
            });
        },'JSON');
    });
}

(function($) {


    if($('.ligne_combine').length){
        $('.ligne_combine select.aller').on('select2:select', function (evt) {
             $(this).siblings('select.retour').focus();
             $(this).siblings('select.retour').select2('open');
        });
    }

    /*DATE PICKER*/
    /*var from = $( ".datepicker-datefrom" )
            .datepicker({
                changeMonth: true,
                changeYear: true,
                monthNames: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],
                monthNamesShort: ['Janv.', 'Févr.', 'Mars', 'Avril', 'Mai', 'Juin', 'Juil.', 'Août', 'Sept.', 'Oct.', 'Nov.', 'Déc.'],
                dayNames: ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'],
                dayNamesShort: ['Dim.', 'Lun.', 'Mar.', 'Mer.', 'Jeu.', 'Ven.', 'Sam.'],
                dayNamesMin: ['D', 'L', 'M', 'M', 'J', 'V', 'S'],
                dateFormat: 'dd-mm-yy',
                firstDay: 1,
                closeText: 'Fermer',
                currentText: 'Aujourd\'hui',
                prevText: '<i class="fa fa-chevron-left"></i>',
                nextText: '<i class="fa fa-chevron-right"></i>',
                showButtonPanel: true
            })
            .on( "change", function() {
                to.datepicker( "option", "minDate", getDate( this ) );
            }),
        to = $( ".datepicker-dateto" ).datepicker({
            changeMonth: true,
            changeYear: true,
            monthNames: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],
            monthNamesShort: ['Janv.', 'Févr.', 'Mars', 'Avril', 'Mai', 'Juin', 'Juil.', 'Août', 'Sept.', 'Oct.', 'Nov.', 'Déc.'],
            dayNames: ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'],
            dayNamesShort: ['Dim.', 'Lun.', 'Mar.', 'Mer.', 'Jeu.', 'Ven.', 'Sam.'],
            dayNamesMin: ['D', 'L', 'M', 'M', 'J', 'V', 'S'],
            dateFormat: 'dd-mm-yy',
            firstDay: 1,
            closeText: 'Fermer',
            currentText: 'Aujourd\'hui',
            prevText: '<i class="fa fa-chevron-left"></i>',
            nextText: '<i class="fa fa-chevron-right"></i>',
            showButtonPanel: true
        })
            .on( "change", function() {
                from.datepicker( "option", "maxDate", getDate( this ) );
            });
*/
    $('#myModal').on('show.bs.modal', function(event)
    {
        opened_modal();
        $('#form_vol_edit .dropdown-toggle').dropdown();
    });

    $('#myModal').on('hidden.bs.modal', function(event)
    {
        closed_modal();
        $('.dropdown-toggle').dropdown();
    });
/*

    // Activation du datepicker de la periode d'ouverture
    /*$('body').delegate('.datepicker-datefrom, .datepicker-dateto',"focusin", function(event) {
     $(this).datepicker({
     changeMonth: true,
     changeYear: true,
     monthNames: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],
     monthNamesShort: ['Janv.', 'Févr.', 'Mars', 'Avril', 'Mai', 'Juin', 'Juil.', 'Août', 'Sept.', 'Oct.', 'Nov.', 'Déc.'],
     dayNames: ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'],
     dayNamesShort: ['Dim.', 'Lun.', 'Mar.', 'Mer.', 'Jeu.', 'Ven.', 'Sam.'],
     dayNamesMin: ['D', 'L', 'M', 'M', 'J', 'V', 'S'],
     dateFormat: 'dd-mm-yy',
     firstDay: 1,
     closeText: 'Fermer',
     currentText: 'Aujourd\'hui',
     showButtonPanel: true,
     });
     });
     $(".datepicker-datefrom").on( "change", function() {
     var test = $(this).datepicker('getDate');
     var testm = new Date(test.getTime());
     testm.setDate(testm.getDate() + 1);
     $(".datepicker-dateto").datepicker("option", "minDate", testm);
     });
     $(".datepicker-dateto").on( "change", function() {
     var test = $(this).datepicker('getDate');
     var testm = new Date(test.getTime());
     testm.setDate(testm.getDate() - 1);
     $(".datepicker-datefrom").datepicker("option", "maxDate", testm);
     });*/
    /* DATE PICKER */

    $('#tout_cocher').click(function() {
        var c = this.checked;
        $(':checkbox').prop('checked',c);
    });

    $('#supprimer_items').click(function (e) {
        var $href = $(this).attr("href");
        e.preventDefault();

        var ids = new Array();
        $.each( $('.check:checked'),function (e) {
            ids.push($(this).val());
        });

        if(0 == ids.length){
            bootbox.alert("<i class='glyphicon glyphicon-alert' style='color: red;'></i> Vous devez cocher au minimum un élément du tableau pour lui appliquer la suppression.");
            return false;
        }else{
            bootbox.confirm({
                message: 'Etes-vous sur de vouloir effectuer cette action ?',
                buttons: {
                    'cancel': {
                        label: 'Annuler',
                        className: 'btn-default pull-left'
                    },
                    'confirm': {
                        label: 'Confirmer',
                        className: 'btn-danger pull-right'
                    }
                },
                callback: function(result) {

                    var ids_item = ids.join('_');

                    if(result && $href){
                        window.location.href = $href+"?ids_item="+ids_item;
                    }
                }
            });
        }

    });

    $('.modal-dialog').draggable();

    $('#myModal').on('show.bs.modal', function () {
        $(this).find('.modal-body').css({
            'max-height':'100%'
        });
    });

    $('input#fos_user_registration_form_roles_0').click(function(){
        if($('input#fos_user_registration_form_roles_1').is(':checked')){
            $('input#fos_user_registration_form_roles_1').attr('checked',false);
        }
    })

    $('input#fos_user_registration_form_roles_1').click(function(){
        if($('input#fos_user_registration_form_roles_0').is(':checked')){
            $('input#fos_user_registration_form_roles_0').attr('checked',false);
        }

    })

    $('input#aircorsica_xkplanbundle_utilisateur_roles_0').click(function(){
        if($('input#aircorsica_xkplanbundle_utilisateur_roles_1').is(':checked')){
            $('input#aircorsica_xkplanbundle_utilisateur_roles_1').attr('checked',false);
        }
    })

    $('input#aircorsica_xkplanbundle_utilisateur_roles_1').click(function(){
        if($('input#aircorsica_xkplanbundle_utilisateur_roles_0').is(':checked')){
            $('input#aircorsica_xkplanbundle_utilisateur_roles_0').attr('checked',false);
        }

    })

    //Pour tester si la ligne existe (combinaison aeroport depart/arivée)
    $('body').delegate('.combine','change', function(){
        loadIdLigne($(this).parent().children('select'));
    });

    $('.new_vol_modal').on('click',function(e){
        e.preventDefault();
        var current_route = $(this).data('route');
        $.ajax({
            type: "GET",
            url: Routing.generate('vol_modal_new'),
            success: function (data) {
                $('#myModal .modal-body').html(data);
                $('#myModal .close').on('click', function () {

                    //correctif pour eviter le bug "datepicker inactif" avec le planningvol lors de la fermeture du modal

                    var urltotest = window.location.pathname;
                    if(urltotest.indexOf('planningvol') !== -1){
                        //window.location.reload();

                        $('#myModal').on('hidden.bs.modal', function () {

                            //on efface le ui-datepicker-div créé par le modal qui pose problème
                            $("#ui-datepicker-div").remove();

                            //on re-initialise le date picker Impression debut pour que datepicker recrée l'element ui-datepicker-div dans le DOM
                            $(".datepickerPrintD").datepicker({
                                monthNames: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],
                                monthNamesShort: ['Janv.', 'Févr.', 'Mars', 'Avril', 'Mai', 'Juin', 'Juil.', 'Août', 'Sept.', 'Oct.', 'Nov.', 'Déc.'],
                                dayNames: ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'],
                                dayNamesShort: ['Dim.', 'Lun.', 'Mar.', 'Mer.', 'Jeu.', 'Ven.', 'Sam.'],
                                dayNamesMin: ['D', 'L', 'M', 'M', 'J', 'V', 'S'],
                                firstDay: 1,
                                dateFormat: 'dd-mm-yy',
                                closeText: 'Fermer',
                                currentText: 'Aujourd\'hui',
                                prevText: '<i class="fa fa-chevron-left"></i>',
                                nextText: '<i class="fa fa-chevron-right"></i>',
                                showButtonPanel: true,
                                changeYear: true,
                                changeMonth: true,
                                yearRange: 'c-10:c+10',
                                showWeek: true,
                                weekHeader: "Sem.",
                                onSelect: function (dateText, inst) {
                                    var printperiodeDebut = dateText;
                                    $('#printdatedebut').val(dateText);
                                    $('#radio_jr').prop("checked", false);
                                    $('#radio_per').prop("checked", true);
                                    if ($('#printdatefin').val() == "") {
                                        $(".datepickerPrintF").datepicker('setDate', moment(dateText, 'DD-MM-YYYY').format('MM-DD-YYYY'));
                                    }
                                }
                            });

                            //on synchronise la date du datepicker
                            var curentmoment = $('#calendar').fullCalendar('getDate');
                            $(".datepicker1").datepicker('setDate', curentmoment.format('MM-DD-YYYY'));
                        });
                    }
                });
                bindSubmitFormVol();
            },
            complete: function () {
                $('#route_to_redirect').val(current_route);
                $('#form_vol_edit .dropdown-toggle').dropdown();
                //$('#aircorsica_xkplanbundle_vol_aeroport_depart').select2('open');
            },
            beforeSend: function () {
                $('#myModal').attr('data-backdrop','static');//empeche de fermer la fenetre en cliquant à l'exterieur du modal
                $('#myModal').modal('toggle');
                $('#myModal .modal-body').html("<div class='progress' id='loadingDiv'><div class='progress-bar progress-bar-striped progress-bar-success active' role='progressbar' \n\
                                         aria-valuenow='100' aria-valuemin='0' aria-valuemax='100' style='width: 100%'></div></div>");
            }
        });
    });

    $('.edit_vol_modal').on('click',function(e){
        e.preventDefault();
        var $link = $(e.target);
        var current_route = $(this).data('route');
        $.ajax({
            //async: true,   // enlève l'erreur 500 (Internal Server Error) au retour de l'ajax
            type: "GET",
            //url: Routing.generate('vol_modification_ponctuel',{id: opt.$trigger.children().data('idvol'), date : opt.$trigger.children().data('date')}),
            url: Routing.generate('vol_modification_ponctuel',{id: $link.data('idvol')}),
            success: function (data) {
                $('#myModal .modal-body').html(data);
                bindSubmitFormVol();
            },
            complete: function () {
                $('#route_to_redirect').val(current_route);
                $('#form_vol_edit .dropdown-toggle').dropdown()
            },
            beforeSend: function () {
                $('#myModal').modal('toggle');
                $('#myModal .modal-body').html("<div class='progress' id='loadingDiv'><div class='progress-bar progress-bar-striped progress-bar-success active' role='progressbar' \n\
                                         aria-valuenow='100' aria-valuemin='0' aria-valuemax='100' style='width: 100%'></div></div>");
            }
        });
    });



    // Place custom scripts here
    // Init jQuery spinner init - default
    $(".spinner").spinner();

    $('.confirm').on("click", function(e){
        var $href = $(this).attr("href");
        e.preventDefault();
        bootbox.confirm({
            message: 'Etes-vous sur de vouloir effectuer cette action ?',
            buttons: {
                'cancel': {
                    label: 'Annuler',
                    className: 'btn-default pull-left'
                },
                'confirm': {
                    label: 'Confirmer',
                    className: 'btn-danger pull-right'
                }
            },
            callback: function(result) {
                if(result && $href){
                    window.location.href = $href;
                }
            }
        });
    });

    $('a[data-toggle="collapse"]').on('click',function(){
        var objectID=$(this).attr('href');
        if($(objectID).hasClass('in'))
        {
            $(objectID).collapse('hide');
        }
        else{
            $(objectID).collapse('show');
        }
    });

    $('#expandAll').on('click',function(){
        $('a[data-toggle="collapse"]').each(function(){
            var objectID=$(this).attr('href');
            if($(objectID).hasClass('in')===false)
            {
                $(objectID).collapse('show');
            }
        });
    });

    $('#collapseAll').on('click',function(){
        $('a[data-toggle="collapse"]').each(function(){
            var objectID=$(this).attr('href');
            $(objectID).collapse('hide');
        });
    });


    $('body').delegate('.add_tag_link','click',function(e){
        /* Initialisation des datepickers */
        init_datepicker(options);
    });

    $('.no-click').on('click',function(e){
        e.preventDefault();
    });

    $('body').delegate('.set-date','click',function(e){
        $(e.target).closest('.input-group-btn').removeClass('open');
        var dateDebut = $(this).data('datedebut');
        var dateFin   = $(this).data('datefin');
        //var key   = $(this).data('key') ;
        $(e.target).closest('.wrapperDatePicker,.wrapperDatePicker-modal').find("input[data-type=debut]").val(dateDebut).trigger("change");
        $(e.target).closest('.wrapperDatePicker,.wrapperDatePicker-modal').find("input[data-type=fin]").val(dateFin).trigger("change");
    });

    $('body').delegate('#aircorsica_xkplanbundle_vol_periodeDeVol_dateDebut,#aircorsica_xkplanbundle_vol_periodeDeVol_dateFin','change',function(e){
        setValues();
    });


    $('body').delegate('.check-jours','click',function(e){
        e.preventDefault();
        $(e.target).closest('.input-group-btn').removeClass('open');
        var role = $(this).data('role');

        switch(role) {
            case 'jour_all':
                $.each($(this).closest('.input-group').find('input.checkbox-jour-semaine'),function(){
                    $(this).prop("checked",true);
                });
                break;

            case 'semaine':
                $.each($(this).closest('.input-group').find('input.checkbox-jour-semaine'),function(){
                    $(this).prop("checked",true);
                });
                $.each($(this).closest('.input-group').find('input.jour-week'),function(){
                    $(this).prop("checked",false);
                });
                break;

            case 'weekend':
                $.each($(this).closest('.input-group').find('input.checkbox-jour-semaine'),function(){
                    $(this).prop("checked",false);
                });
                $.each($(this).closest('.input-group').find('input.jour-week'),function(){
                    $(this).prop("checked",true);
                });
                break;

            case 'jour_none':
                $.each($(this).closest('.input-group').find('input.checkbox-jour-semaine'),function(){
                    $(this).prop("checked",false);
                });
                break;
        }
        setValues();
    });

    $('body').delegate('.validForm','click',function(e){
        if($('form').find('span.bck-red').length){
            e.preventDefault();
            messageErreurForm();
        }
    });

    // $('#jour_none').click(function(){
    //     $('.jour_none').prop("checked",false);
    // });
    //
    // $('#semaine').click(function(){
    //     $('.lun').prop("checked",true);
    //     $('.mar').prop("checked",true);
    //     $('.mer').prop("checked",true);
    //     $('.jeu').prop("checked",true);
    //     $('.ven').prop("checked",true);
    // });
    //
    // $('#weekend').click(function(){
    //     $('.sam').prop("checked",true);
    //     $('.dim').prop("checked",true);
    // });

    $('#aircorsica_xkplanbundle_parametres_form').on('submit',function(e){

        e.preventDefault();
        var parametres_messagereplyaddress = $("#aircorsica_xkplanbundle_parametres_messagereplyaddress").val();
        var parametres_codeaeroportdattacheaircorsica = $("#aircorsica_xkplanbundle_parametres_codeaeroportdattacheaircorsica").val();
        var parametres_codeemetteursita = $("#aircorsica_xkplanbundle_parametres_codeemetteursita").val();
        var parametres_adressesitaaltea = $("#aircorsica_xkplanbundle_parametres_adressesitaaltea").val();
        var parametres_emailsitaaltea = $("#aircorsica_xkplanbundle_parametres_emailsitaaltea").val();
        var parametres_emailsitassim7 = $("#aircorsica_xkplanbundle_parametres_emailsitassim7").val();

        if(parametres_messagereplyaddress==''||parametres_codeaeroportdattacheaircorsica==''||parametres_codeemetteursita==''||parametres_adressesitaaltea==''||parametres_emailsitaaltea==''||parametres_emailsitassim7==''){
            bootbox.alert({
                message: 'Merci de remplir tous les champs afin d\'enregistrer les paramètres globaux',
                buttons: {
                    'ok': {
                        label: 'J\'ai compris',
                        className: 'btn-danger pull-right'
                    },
                },
            });
        }else{
            $.ajax({
                type: "POST",
                dataType: 'json',
                url: Routing.generate('parametres_saveparameters'),
                data: {
                    parametres_messagereplyaddress: parametres_messagereplyaddress,
                    parametres_codeaeroportdattacheaircorsica: parametres_codeaeroportdattacheaircorsica,
                    parametres_codeemetteursita: parametres_codeemetteursita,
                    parametres_adressesitaaltea: parametres_adressesitaaltea,
                    parametres_emailsitaaltea: parametres_emailsitaaltea,
                    parametres_emailsitassim7: parametres_emailsitassim7,
                },
                cache: false,
                success: function (data) {
                    if(!data.valide){
                        alert("Erreur Enregistrement Fichier!");
                    }else{
                        //on recharge la page
                        window.location.reload();
                    }
                        /*else{
                        $("#divID").html('<div class="alert alert-success alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button><i class="fa fa-check pr10"></i>'+data.response+'</div>');
                    }*/
                }
            });
        }
        return false;
    });

})(jQuery);

//Permet d'activer le tab souhaité
function activaTab(tab){
    $('.nav a[href="#' + tab + '"]').tab('show');
};

function loadIdLigne(selectDepartArrivee){
    var aeroport_depart = null;
    var aeroport_arrivee = null;
    var aeroport = new Array;
    $(selectDepartArrivee).each(function () {
        aeroport.push($(this));
    })

    aeroport_depart = aeroport[0].find('option:selected').data('aeroport-depart');
    aeroport_arrivee = aeroport[1].find('option:selected').data('aeroport-arrivee');

    if(!aeroport_depart || !aeroport_arrivee){
        return;
    }else{
        $.ajax({
            type: 'get',
            url: Routing.generate('vol_getidligne', { aeroport_depart : aeroport_depart, aeroport_arrivee: aeroport_arrivee}),
            success: function (data) {
                if(data == null){
                    $(aeroport[0]).next().children('span.selection').children().addClass('bck-red');
                    $(aeroport[1]).next().children('span.selection').children().addClass('bck-red');
                    // $("span[aria-labelledby='select2-aircorsica_xkplanbundle_vol_aeroport_depart-container']").css('background-color','red');
                    // $("span[aria-labelledby='select2-aircorsica_xkplanbundle_vol_aeroport_arrivee-container']").css('background-color','red');
                }else{
                    $(aeroport[0]).next().children('span.selection').children().css('background-color','#f0f0f0');
                    $(aeroport[1]).next().children('span.selection').children().css('background-color','#f0f0f0');
                    $(aeroport[0]).next().children('span.selection').children().removeClass('bck-red');
                    $(aeroport[1]).next().children('span.selection').children().removeClass('bck-red');
                    // $("span[aria-labelledby='select2-aircorsica_xkplanbundle_vol_aeroport_arrivee-container']").css('background-color','#f0f0f0');
                    // $("span[aria-labelledby='select2-aircorsica_xkplanbundle_vol_aeroport_arrivee-container']").css('background-color','#f0f0f0');
                }
            }
        });
    }
}

function messageErreurForm(){
    bootbox.alert({
        message: 'Attention, vous avez une ou des erreurs dans la saisie des données de votre formulaire. Veuillez faire le nécessaire avant valider celui-ci.',
        buttons: {
            'ok': {
                label: 'J\'ai compris',
                className: 'btn-danger pull-right'
            },
        },
    });
}

