'use strict';

//periodedebut string au format "YYYY-MM-DD"
//periodefin string au format "YYYY-MM-DD"
//tableaudejours array au format [0,2,5,6]
function findnewperiodestartendinfunctionofdaysofweekselected(periodedebut, periodefin, tableaudejours){

    //on transforme les date au format text en moment pour pouvoir faire des calculs
    var periodedatedebut = moment(periodedebut);
    var periodedatefin = moment(periodefin);

    //les jours de la semaine avec moment.js partent de 1 pour lundi à 7 pour dimanche
    //le tableau "tableaudejours" a des jours qui partent de 0 pour lundi à 6 pour dimanceh
    var jourdebutperiode = periodedatedebut.weekday(); //eval(periodedatedebut.isoWeekday()-1);
    var jourfinperiode = periodedatefin.weekday(); //eval(periodedatefin.isoWeekday()-1);

    // Est-ce que le jour de début de la période est valide
    //-----------------------------------------------------
    var jourvalide = false;
    tableaudejours.forEach(function(unjour) {
        if(unjour == jourdebutperiode){
            jourvalide = true;
        }
    });

    //-----------------------------------
    // recherche du nouveau jour de début
    //-----------------------------------
    if(jourvalide == false) { //le jour du début de période n'est pas inclus dans le tableau "tableaudejours"

        var nbdejoursaajouter = 0;
        Loop1:
            for (var val_jr = jourdebutperiode; val_jr <= 6; val_jr++) {
                for (var idx_arr = 0; idx_arr <= tableaudejours.length; idx_arr++) {
                    if (val_jr == tableaudejours[idx_arr]) {
                        // le jour est dans le tableau de jour
                        break Loop1;
                    }
                }
                nbdejoursaajouter++;
                if (val_jr == 6) {
                    val_jr = -1;//au relancement de la boucle val_jr sera égale à 0
                }
            }
        periodedatedebut.add(nbdejoursaajouter, 'd');

    }else{
        //le jour du debutperiode est dans le tableau on ne change pas la date
    }


    // Est-ce que le jour de début de la période est valide
    //-----------------------------------------------------
    var jourvalide = false;
    tableaudejours.forEach(function(unjour) {
        if(unjour == jourfinperiode){
            jourvalide = true;
        }
    });

    //-----------------------------------
    // recherche du nouveau jour de fin
    //-----------------------------------
    if(jourvalide == false) { //le jour du début de période n'est pas inclus dans le tableau "tableaudejours"

        var nbdejoursaenlever = 0;
        Loop2:
            for (var val_jr = jourfinperiode; val_jr >= 0; val_jr--) {
                for (var idx_arr = 0; idx_arr <= tableaudejours.length; idx_arr++) {
                    if (val_jr == tableaudejours[idx_arr]) {
                        // le jour est dans le tableau de jour
                        break Loop2;
                    }
                }
                nbdejoursaenlever++;
                if (val_jr == 0) {
                    val_jr = 7;//au relancement de la boucle val_jr sera égale à 6
                }
            }
        periodedatefin.subtract(nbdejoursaenlever, 'd');

    }else{
        //le jour de la finperiode est dans tableau on ne change pas la date
    }

    //on retourne la nouvelle période compatible array[0] debut et array[1] fin
    var NewPeriodresult = new Array();
    NewPeriodresult['debut'] = periodedatedebut.format("YYYY-MM-DD");
    NewPeriodresult['fin'] = periodedatefin.format("YYYY-MM-DD");
    return NewPeriodresult;

}


function isAmbiguousRessourceTarget(aoldressources/*origineRessource*/,targetRessource){

     var ambiguous = true;

    // on vérifie que les avions sont de meme type
    aoldressources.forEach(function(uneresource) {
        if(uneresource.type != targetRessource.type){
            ambiguous = false;
        }
    });

     //if(origineRessource.type == targetRessource.type){
     //allowed = true;
     //}else{
     //allowed = false;
     //alert('Modification impossible l\'avion cible '+targetRessource.title+' n\'est pas du même type que celui d\'origine '+origineRessource.title+'.');
     //}

    // TODO à décommenté pour n'autorisé le déplacement d'un vol que sur un avion du même type
     return ambiguous; //l'avion cible est de type différent qu'un des avions d'origine

}

function majPlanningVolParameters(interactionsouris,selectedeventsarray,dateeditionplanning,zoom_y,typedemodification, typedeplanning, periodecustomdebut, periodecustomfin, selectedIdSaison){

    if(selectedIdSaison==""){
        selectedIdSaison=$(this).find(":selected").val();
    }

    var url = Routing.generate('planningvol_setparametresplanningvol');

    //on modifie la variable de session du templatecourant
    $.ajax({
        url: url,
        dataType: 'json',
        type: 'POST',
        data: {
            'typedeplanning': typedeplanning, //journalier ou hebdomadaire
            'periodecustomdebut': periodecustomdebut,
            'periodecustomfin': periodecustomfin,
            'typedemodificationplanning': typedemodification,
            'interactionsourisplanning': interactionsouris,
            'aselectionvolsplanning': selectedeventsarray,
            'dateplanningencouredition': dateeditionplanning,
            'zoominterface': zoom_y,
            'selectedIdSaison': selectedIdSaison
        },
        success: function(doc) {
            //success
        },
        error: function(){
        }
    });
}


(function($) {

    var laskKeyCodePressed;

    function allowDropOfOneParticularMultiselectEvent(eventaverifier,targetRessource,eventquelondeplace){

        //on récupére les events(vols) éxistant sur la ressource(avion) cible
        var aEventsDeLaRessourceCible = $('#calendar').fullCalendar('getResourceEvents',targetRessource);
        var allowed = true;

        // on vérifie que l'interval de temps necessaire au déplacement de l'event n'est pas déja utilisé par un vol de la ressource cible
        aEventsDeLaRessourceCible.forEach(function(unevent) {
            if (unevent.id != eventquelondeplace.id) {//si ce n'est pas l'event que l'on déplace avec la souris

                if ( (
                        ( ( moment(unevent.start).isBefore(moment(eventaverifier.end)) ) &&
                        ( moment(unevent.end).isAfter(moment(eventaverifier.end)) ) ) ||
                        ( ( moment(unevent.start).isBefore(moment(eventaverifier.start)) ) &&
                        ( moment(unevent.end).isAfter(moment(eventaverifier.start)) ) )
                    ) || (
                        moment(unevent.start).isSame(moment(eventaverifier.start))
                    )
                ){
                    allowed = false;
                }

            }
        });
        return allowed; //on a le droit de le déplacer ou pas
    }

    $.contextMenu({

        selector: '.td_avion',
        autoHide: true,
        items: {
            nouveauvol: {
                name: "Création d'un nouveau vol",
                callback: function(key, opt){
                    var elm = opt.$trigger.parent();
                    var idavion = elm.attr('data-resource-id');
                    var dateDuJour = $('#calendar').fullCalendar('getDate').format('DD-MM-YYYY');
                    //si il y a plusieur jours selectionné
                    var joursSemaine = new Array();

                    // TODO: A décommenté pour ajouter la fonctionnalité des selection multiples de jours
                    /*
                     if(selectedjoursplanninghebdomadairearray.length > 0) {
                     for(var i=0; i<7;i++){
                     if(selectedjoursplanninghebdomadairearray[i] == null){
                     joursSemaine[i] = '-';
                     }else{
                     joursSemaine[i] = eval(i+1);
                     }
                     }
                     //joursSemaine = selectedjoursplanninghebdomadairearray;
                     }else {
                     //sinon
                     */

                    for(var i=0; i<7;i++){
                        if(i != $('#calendar').fullCalendar('getDate').format('e')) {
                            joursSemaine[i] = '-';
                        }else{
                            joursSemaine[i] = eval(i+1);
                        }
                    }

                    /*} */
                    // fin du TODO
                    $.ajax({
                        async: true,   // enlève l'erreur 500 (Internal Server Error) au retour de l'ajax
                        type: "GET",
                        url: Routing.generate('vol_new',{idAvion : idavion,  dateDuJour : dateDuJour, joursSemaine : joursSemaine.toString()}),
                        success: function (data) {
                            $('#myModal .modal-body').html(data);
                            bindSubmitFormVol();
                        },
                        complete: function () {
                            $('#route_to_redirect').val('planningvol_show');
                            $('#form_vol_edit .dropdown-toggle').dropdown();
                        },
                        beforeSend: function () {
                            $('#myModal').unbind();
                            $('#myModal').attr('data-backdrop','static');//empeche de fermer la fenetre en cliquant à l'exterieur du modal
                            $('#myModal').modal('toggle');
                            $('#myModal .modal-body').html("<div class='progress' id='loadingDiv'><div class='progress-bar progress-bar-striped progress-bar-success active' role='progressbar' \n\
                                         aria-valuenow='100' aria-valuemin='0' aria-valuemax='100' style='width: 100%'></div></div>");
                            $('#myModal .close').on('click', function () {
                                //window.location.reload();
                                $('#myModal').on('hidden.bs.modal', function () {
                                    //enleve le bug des datepicker qui sont inactifs à la sortie du modal
                                    resetDatePickerWhenExitingAjaxLoadedModal();
                                });
                            });

                        }
                    });

                },
                disabled: function() {
                    if ((interactionsouris == false) && (userisgranted == true)) {
                        return false;
                    }else{
                        return true;
                    }
                    //return !userisgranted;
                }
            }
        }

    });

    $.contextMenu({
        selector: '.fc-timeline-event',
        autoHide: true,
        items: {
            modificationponctuel: {
                name: "Modification ponctuelle",
                callback: function(key, opt){

                    var url = Routing.generate('planningvol_populatevolinmodifponctuellemodal');
                    var levol = $("#calendar").fullCalendar('clientEvents', opt.$trigger.children().data('idvol'));

                    $.ajax({
                        async: true,   // enlève l'erreur 500 (Internal Server Error) au retour de l'ajax
                        type: "POST",
                        url: url,
                        data: {
                            'idVol': opt.$trigger.children().data('idvol'),
                            'dateModifPonctuelle': opt.$trigger.children().data('date')
                        },
                        success: function (data) {
                            $('#myModifJournalierePonctuelleLabel').html('<i class="glyphicon glyphicon-edit fa-lg"></i> <strong>MODIFICATION PONCTUELLE DU VOL '+levol[0].codeVol+'</strong>');
                            $('#myModifJournalierePonctuelleHeader').css('display',''); //affiche le titre
                            $('#myModifJournalierePonctuelleFooter').css('display',''); //affiche les actions
                            $('#myModifJournalierePonctuelleBody').html(data); //affiche les infos du vol
                        },
                        complete: function () {
                            bindEventsModifVoPonctuelleModal();
                        },
                        beforeSend: function () {
                            $('#myModifJournalierePonctuelleModal').unbind();
                            $('#myModifJournalierePonctuelleBody').html("<div class='progress' id='loadingModifPonctuelleDiv' style='margin-top:20px;'><div class='progress-bar progress-bar-striped progress-bar-success active' role='progressbar' aria-valuenow='100' aria-valuemin='0' aria-valuemax='100' style='width: 100%;'></div></div>");
                            $('#myModifJournalierePonctuelleModal').modal('toggle');
                        }
                    });

                },
                disabled: function(key, opt) {
                    if ((interactionsouris == false) && (userisgranted == true)) {
                        if( opt.$trigger.children().data('immo') == true){
                            return true;
                        }/*else{ // Empéche de faire une autre action si ce mol a été modifié et les messages générés ni envoyés ni acquitté
                            if( opt.$trigger.children().data('msgenvoye') == false){
                                return true;
                            }else {
                                return false;
                            }
                        }*/
                        return false;
                    }else{
                        return true;
                    }
                    //return !userisgranted;
                }

            },
            modificationglobale: {
                name: "Modification globale",
                callback: function(key, opt){

                    $.ajax({
                        async: true,   // enlève l'erreur 500 (Internal Server Error) au retour de l'ajax
                        type: "GET",
                        url: Routing.generate('vol_modification_globale',{id: opt.$trigger.children().data('idvol')}),
                        success: function (data) {
                            $('#myModal .modal-body').html(data);
                        },
                        complete: function () {
                            $('#route_to_redirect').val('planningvol_show');
                            $('#form_vol_edit .dropdown-toggle').dropdown();
                        },
                        beforeSend: function () {
                            $('#myModal').unbind();
                            $('#myModal').attr('data-backdrop','static');//empeche de fermer la fenetre en cliquant à l'exterieur du modal
                            $('#myModal').modal('toggle');
                            $('#myModal .modal-body').html("<div class='progress' id='loadingDiv'><div class='progress-bar progress-bar-striped progress-bar-success active' role='progressbar' \n\
                                         aria-valuenow='100' aria-valuemin='0' aria-valuemax='100' style='width: 100%'></div></div>");
                            $('#myModal .close').on('click', function () {
                                //window.location.reload();
                                $('#myModal').on('hidden.bs.modal', function () {
                                    //enleve le bug des datepicker qui sont inactifs à la sortie du modal
                                    resetDatePickerWhenExitingAjaxLoadedModal();
                                });
                            });
                        }
                    });

                },
                disabled: function(key, opt) {
                    if ((interactionsouris == false) && (userisgranted == true)) {
                        if( opt.$trigger.children().data('immo') == true){
                            return true;
                        }/*else{ // Empéche de faire une autre action si ce mol a été modifié et les messages générés ni envoyés ni acquitté
                            if( opt.$trigger.children().data('msgenvoye') == false){
                                return true;
                            }else {
                                return false;
                            }
                        }*/
                        return false;
                    }else{
                        return true;
                    }
                    //return !userisgranted;
                }

            },
            informationsduvol: {
                name: "Quick informations vol",
                callback: function(key, opt){

                    var url = Routing.generate('planningvol_populatevolinvisualisationinfosmodal');
                    var levol = $("#calendar").fullCalendar('clientEvents', opt.$trigger.children().data('idvol'));

                    $.ajax({
                        async: true,   // enlève l'erreur 500 (Internal Server Error) au retour de l'ajax
                        type: "POST",
                        url: url,
                        data: {
                            'idVol': opt.$trigger.children().data('idvol')
                        },
                        success: function (data) {
                            $('#myModifJournalierePonctuelleLabel').html('<i class="glyphicon glyphicon-edit fa-lg"></i> <strong>INFORMATIONS DU VOL '+levol[0].codeVol+'</strong>');
                            $('#myVisualisationVolModalHeader').css('display',''); //affiche le titre
                            $('#myVisualisationVolModalFooter').css('display',''); //affiche les actions
                            $('#myVisualisationVolModalBody').html(data); //affiche les infos du vol
                        },
                        complete: function () {
                            bindEventsVisualisationVolModal();
                        },
                        beforeSend: function () {
                            $('#myVisualisationVolModal').unbind();
                            $('#myVisualisationVolModalBody').html("<div class='progress' id='loadingModifPonctuelleDiv' style='margin-top:20px;'><div class='progress-bar progress-bar-striped progress-bar-success active' role='progressbar' aria-valuenow='100' aria-valuemin='0' aria-valuemax='100' style='width: 100%;'></div></div>");
                            $('#myVisualisationVolModal').modal('toggle');
                        }
                    });

                },
                disabled: function(key, opt) {
                    if ((interactionsouris == false) && (userisgranted == true)) {
                        if( opt.$trigger.children().data('immo') == true){
                            return true;
                        }/*else{ // Empéche de faire une autre action si ce mol a été modifié et les messages générés ni envoyés ni acquitté
                         if( opt.$trigger.children().data('msgenvoye') == false){
                         return true;
                         }else {
                         return false;
                         }
                         }*/
                        return false;
                    }else{
                        return true;
                    }
                    //return !userisgranted;
                }

            },
            delestageponctuel: {
                name: "Delestage ponctuel",
                callback: function(key, opt){

                    var levol = $("#calendar").fullCalendar('clientEvents', opt.$trigger.children().data('idvol'));

                    bootbox.confirm(
                        {
                            message :   "<p><i class='glyphicon glyphicon-alert info'></i> Désirez-vous vraiment delester le vol " +levol[0].codeVol+" pour le "+$("h2").text()+".</p>",

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
                                    var elm = opt.$trigger.children();
                                    var idVol  =  elm.data('idvol');
                                    var dateVol  =  elm.data('date');
                                    window.location.href = Routing.generate('vol_delestage_ponctuel',{id : idVol, date : dateVol});
                                }
                            }
                        }

                    );

                },
                disabled: function(key, opt) {
                    if ((interactionsouris == false) && (userisgranted == true)) {
                        if( opt.$trigger.children().data('immo') == true){
                            return true;
                        }/*else{ // Empéche de faire une autre action si ce mol a été modifié et les messages générés ni envoyés ni acquitté
                            if( opt.$trigger.children().data('msgenvoye') == false){
                                return true;
                            }else {
                                return false;
                            }
                        }*/
                        return false;
                    }else{
                        return true;
                    }
                    //return !userisgranted;
                }

            },
            delestagePeriode: {
                name: "Delestage de la totalité de la période",
                callback: function(key, opt){

                    var levol = $("#calendar").fullCalendar('clientEvents', opt.$trigger.children().data('idvol'));

                    bootbox.confirm(
                        {

                            message :   "<p><i class='glyphicon glyphicon-alert info'></i> Désirez-vous vraiment delester le vol " +levol[0].codeVol+" pour la totalité de sa période de vol.</p>",

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
                                    var elm = opt.$trigger.children();
                                    var idVol  =  elm.data('idvol');
                                    window.location.href = Routing.generate('vol_delestage_periode',{id : idVol});
                                }
                            }
                        }

                    );

                },
                disabled: function(key, opt) {
                    if ((interactionsouris == false) && (userisgranted == true)) {
                        if( opt.$trigger.children().data('immo') == true){
                            return true;
                        }/*else{ // Empéche de faire une autre action si ce mol a été modifié et les messages générés ni envoyés ni acquitté
                            if( opt.$trigger.children().data('msgenvoye') == false){
                                return true;
                            }else {
                                return false;
                            }
                        }*/
                        return false;
                    }else{
                        return true;
                    }
                    //return !userisgranted;
                }

            }

        }
    });

    //---------------------------------------------
    //              Evenement
    // click sur les jours du planning hebdomadaire
    //---------------------------------------------

    $('div[id^="BlockPlanningHebdo_"]').on('click', function () {

        var day = $(this).attr('id').split('_')[1];
        var monElementDateClique = $('#PlHebd_'+day);

        if(interactionsouris==true) {

            var datedetravail = moment($('#calendar').fullCalendar('getDate'), 'YYYY-MM-DD');
            var query = eval(parseInt(monElementDateClique.attr('data-weekday')) - datedetravail.weekday());
            var datejourclick = datedetravail.add(query, 'days');

            if (monElementDateClique.hasClass('joursem_sel')) {
                monElementDateClique.removeClass('joursem_sel');
                monElementDateClique.children().first().next().remove();
                delete selectedjoursplanninghebdomadairearray[monElementDateClique.attr('data-weekday')];
            } else {
                monElementDateClique.addClass('joursem_sel');
                monElementDateClique.append('<i class="glyphicon glyphicon-ok-circle fa-lg croixdeselectionjoursem_sel" style="font-size:1.5em;color:'+couleurDeSelectionDesJoursSemaine+';"></i>');
                monElementDateClique.attr('data-datedecejour', datejourclick.format('DD-MM-YYYY'));
                selectedjoursplanninghebdomadairearray[monElementDateClique.attr('data-weekday')] = datejourclick.format('DD-MM-YYYY');
            }


        }else{ //si ont est pas en mode selection

            $('#calendar').fullCalendar('gotoDate', moment(monElementDateClique.attr('data-datedecejour'),'DD-MM-YYYY'));

            //on met a jour la var de session
            var curentmoment = $('#calendar').fullCalendar('getDate');
            selectedeventsarray = [];
            majPlanningVolParameters(interactionsouris,selectedeventsarray,curentmoment.format('DD-MM-YYYY'),zoom_y,typedemodificationplanning,typedeplanning,periodecustomdebut,periodecustomfin,selectedIdSaison);

            //on deselectionne tous les jour de la semaines du planning hebdomadaire
            $('div[id^="PlHebd_"]').each(function(){
                $(this).removeClass('badge');
                $(this).removeClass('badge-success');
            });

            //on deselectionne le jour de la semaine du planning hebdomadaire
            $("#PlHebd_"+curentmoment.format('e')).addClass('badge');
            $("#PlHebd_"+curentmoment.format('e')).addClass('badge-success');

        }

    });


    //--------------------------------------------------
    //              Evenement
    // Click sur ponctuelle du switch ponctuelle/saison/période
    //--------------------------------------------------

    $('#sw_ponctuelle').on('click', function () {

        if(userisgranted == true) {

            if (!$('#sw_ponctuelle').hasClass('btn-success')) { //empéche le clic sur le bouton PONCTUELLE selectionné

                $('#sw_saisoncourante').removeClass('btn-success');
                $('#sw_saisoncourante').addClass('btn-default');
                $('#sw_saisoncourante').css('cursor', 'pointer');

                $('#sw_periode').removeClass('btn-success');
                $('#sw_periode').addClass('btn-default');
                $('#sw_periode').css('cursor', 'pointer');

                $('#bloccustomdatedebut').css('display', 'none');
                $('#bloccustomdatefin').css('display', 'none');
                $('#periodecustomdatedebut').attr('disabled', 'disabled');
                $('#periodecustomdatedebut').val('');
                $('#periodecustomdatefin').attr('disabled', 'disabled');
                $('#periodecustomdatefin').val('');
                periodecustomdebut = "";
                periodecustomfin = "";

                $(this).removeClass('btn-default');
                $(this).addClass('btn-success');
                $(this).css('cursor', 'default');

                typedemodificationplanning = 0;

                var curentmoment = $('#calendar').fullCalendar('getDate');
                selectedeventsarray = [];
                majPlanningVolParameters(interactionsouris, selectedeventsarray, curentmoment.format('DD-MM-YYYY'), zoom_y, typedemodificationplanning, typedeplanning, periodecustomdebut, periodecustomfin,selectedIdSaison);

            }

        }

    });


    //------------------------------------------------
    //              Evenement
    // // Click sur saison du switch ponctuelle/saison/période
    //------------------------------------------------

    $('#sw_saisoncourante').on('click', function () {

        if(userisgranted == true) {

            if (!$('#sw_saisoncourante').hasClass('btn-success')) { //empéche le clic sur le bouton SAISON selectionné

                $('#sw_ponctuelle').removeClass('btn-success');
                $('#sw_ponctuelle').addClass('btn-default');
                $('#sw_ponctuelle').css('cursor', 'pointer');

                $('#sw_periode').removeClass('btn-success');
                $('#sw_periode').addClass('btn-default');
                $('#sw_periode').css('cursor', 'pointer');

                $('#bloccustomdatedebut').css('display', 'none');
                $('#bloccustomdatefin').css('display', 'none');
                $('#periodecustomdatedebut').attr('disabled', 'disabled');
                $('#periodecustomdatedebut').val('');
                $('#periodecustomdatefin').attr('disabled', 'disabled');
                $('#periodecustomdatefin').val('');
                periodecustomdebut = "";
                periodecustomfin = "";

                $(this).removeClass('btn-default');
                $(this).addClass('btn-success');
                $(this).css('cursor', 'default');

                typedemodificationplanning = 1;

                var curentmoment = $('#calendar').fullCalendar('getDate');
                selectedeventsarray = [];
                majPlanningVolParameters(interactionsouris, selectedeventsarray, curentmoment.format('DD-MM-YYYY'), zoom_y, typedemodificationplanning, typedeplanning, periodecustomdebut, periodecustomfin,selectedIdSaison);

            }

        }

    });


    //------------------------------------------------
    //              Evenement
    // // Click sur période du switch ponctuelle/saison/période
    //------------------------------------------------

    $('#sw_periode').on('click', function () {

        if(userisgranted == true) {

            if (!$('#sw_periode').hasClass('btn-success')) { //empéche le clic sur le bouton PERIODE selectionné

                $('#sw_ponctuelle').removeClass('btn-success');
                $('#sw_ponctuelle').addClass('btn-default');
                $('#sw_ponctuelle').css('cursor', 'pointer');

                $('#sw_saisoncourante').removeClass('btn-success');
                $('#sw_saisoncourante').addClass('btn-default');
                $('#sw_saisoncourante').css('cursor', 'pointer');

                $('#bloccustomdatedebut').css('display', '');
                $('#bloccustomdatefin').css('display', '');
                $('#periodecustomdatedebut').removeAttr('disabled');
                $('#periodecustomdatefin').removeAttr('disabled');
                $('#periodecustomdatedebut').val('');
                $('#periodecustomdatefin').val('');

                $(this).removeClass('btn-default');
                $(this).addClass('btn-success');
                $(this).css('cursor', 'default');

                typedemodificationplanning = 2;

                var curentmoment = $('#calendar').fullCalendar('getDate');
                selectedeventsarray = [];
                majPlanningVolParameters(interactionsouris, selectedeventsarray, curentmoment.format('DD-MM-YYYY'), zoom_y, typedemodificationplanning, typedeplanning, periodecustomdebut, periodecustomfin,selectedIdSaison);

            }

        }

    });


    // Init Select2 - Basic Single
    $(".select2-single").select2({
        language: "fr"
    });


    //---------------------------------------
    //     Modification Vol Ponctuelle
    //---------------------------------------
    $('#modifjournaliereponctuelleANNULER').click(function() {
        $('#myModifJournalierePonctuelleHeader').css('display','none');
        $('#myModifJournalierePonctuelleFooter').css('display','none');
    });

    function recalcultempsdevol(decollage,atterissage){

        var url = Routing.generate('planningvol_getmodalnewatterrisagetimeandtempdevol');

        if(atterissage==null) {
            $('#modifvolponctuelle_atterissage').val('re-calcul...');
        }
        if(decollage==null){
            $('#modifvolponctuelle_decollage').val('re-calcul...');
        }

        $('#modifjournaliereponctuelleEXECUTER').addClass('disabled');
        //$('#modifjournaliereponctuelleANNULER').addClass('disabled');

        $.ajax({
            async: true,   // enlève l'erreur 500 (Internal Server Error) au retour de l'ajax
            dataType: 'json',
            type: "POST",
            url: url,
            data: {
                'idVol':$('#modifvolponctuelle_idvol').val(), //le vol a modifier
                'idAvion': $('#modifvolponctuelle_avion').val(),//valeur du select avion
                'decollage': decollage, //valeur du champs decollage
                'atterissage': atterissage,
                'dateModifPonctuelle': $('#modifvolponctuelle_datedebut').val() //date de la modif ponctuelle
            },
            success: function (data) {
                var jsonobj = $.parseJSON(data);
                $('#modifvolponctuelle_avion').trigger('focus');
                //$('#modifjournaliereponctuelleANNULER').removeClass('disabled');
                if(jsonobj.dureevol != 0 ) {
                    $('#modifvolponctuelle_decollage').val(jsonobj.decollage);
                    $('#modifvolponctuelle_atterissage').val(jsonobj.atterissage);
                    $('#infos-tdv').html(jsonobj.dureeheures + 'heure(s) et ' + jsonobj.dureeminutes + 'minute(s)');
                    $('#modifjournaliereponctuelleEXECUTER').removeClass('disabled');
                }else{
                    $('#infos-tdv').html('<span style="color:red;">l\'avion '+$("#modifvolponctuelle_avion option:selected").text()+' n\'a pas de durée de vol définie pour cette ligne au '+$("#modifvolponctuelle_datedebut").val()+'</span>');
                    $('#modifvolponctuelle_atterissage').val('erreur!!!');
                }
            },
            error: function () {
                $('#modifvolponctuelle_avion').trigger('focus');
                $('#infos-tdv').html('<span style="color:red;">l\'avion '+$("#modifvolponctuelle_avion option:selected").text()+' n\'a pas de durée de vol définie pour cette ligne au '+$("#modifvolponctuelle_datedebut").val()+'</span>');
                $('#modifvolponctuelle_atterissage').val('erreur!!!');
            },
            complete: function () {

            },
            beforeSend: function () {

            }
        });

    }

    function bindEventsVisualisationVolModal() {

        //fermeture du modal
        $('#myVisualisationVolModalFERMER').on('click', function() {
            $('#myVisualisationVolModal').on("hidden.bs.modal", function () {

                $('#myVisualisationVolModalFERMER').unbind('click');
                $('#myVisualisationVolModalHeader').css('display','none');
                $('#myVisualisationVolModalFooter').css('display','none');

            });
        });

    }

    function bindEventsModifVoPonctuelleModal() {

        //event modification de l'heure de décollage
        $('#modifvolponctuelle_decollage').on("change", function () {
            //alert('chnage decollage');
        });

        $('input[name=typesaisiehoraire]').change(function() {
            //raz des valeur d'origine décollage/attérissage
            $('#modifvolponctuelle_decollage').val( $('#modificationponctuelle_decollageinitial').val() );
            $('#modifvolponctuelle_atterissage').val( $('#modificationponctuelle_atterissageinitial').val() );
            $('#modifvolponctuelle_avion').val( $('#modifvolponctuelle_idancienavion').val() );
            //message
            //alert( "Le mode de saisie a été modifié.\nles décollage/attérissage ont étés remis à leur état d'origine." );
            bootbox.alert("Le mode de saisie a été modifié.\nl'avion et les décollage/attérissage ont étés remis à leur état d'origine.");
        });

        $('#modifvolponctuelle_decollage').focus(function (e) {
            $('#modifjournaliereponctuelleEXECUTER').addClass('disabled');
            //$('#modifjournaliereponctuelleANNULER').addClass('disabled');
            $('#modifvolponctuelle_decollage').val('');
        });
        //event controle saisie de l'heure de décollage
        $('#modifvolponctuelle_decollage').keypress(function (e) { // password written
            var fieldcontent = $('#modifvolponctuelle_decollage').val();

            if (fieldcontent.length < 4) {
                if ((parseInt(e.charCode) > 47) && (parseInt(e.charCode) < 58)) {
                    if (fieldcontent.length == 2) {
                        fieldcontent = fieldcontent + ":";
                        $('#modifvolponctuelle_decollage').val(fieldcontent);
                    }
                } else if ( (laskKeyCodePressed == 8) && (fieldcontent.length == 4) ){
                    $('#modifvolponctuelle_decollage').val(fieldcontent.slice(0, -1));
                } else {
                    return false;
                }
            } else if (fieldcontent.length == 4) {

                if ((parseInt(e.charCode) > 47) && (parseInt(e.charCode) < 58)) {

                    var test = $('#modifvolponctuelle_decollage').val()+String.fromCharCode(e.charCode);
                    var temptest = test.split(":");
                    var testhours = parseInt(temptest[0]);
                    var testminutes = parseInt(temptest[1]);

                    if( (testhours<24) && (testminutes<60) ) {
                        if($('input[name=typesaisiehoraire]:checked').val() == 0) {
                            recalcultempsdevol($('#modifvolponctuelle_decollage').val() + String.fromCharCode(e.charCode), null);
                        }else{
                            var numberDec = parseInt( test.replace(":", "") );
                            var test2 = $('#modifvolponctuelle_atterissage').val();
                            var numberAtt = parseInt( test2.replace(":", "") );

                            if(numberDec<numberAtt) {
                                $('#modifjournaliereponctuelleEXECUTER').removeClass('disabled');
                            }
                        }
                    }else{
                        $('#modifvolponctuelle_decollage').val('erreur!!!');
                        $('#modifvolponctuelle_avion').trigger('focus');
                        return false; //l'horaire n'est pas valide
                    }

            } else if ( (laskKeyCodePressed == 8) && (fieldcontent.length == 4) ){
                $('#modifvolponctuelle_decollage').val(fieldcontent.slice(0, -1));
            } else {
                return false;
            }

            } else {
                return false;
            }
        });
        //detection de l'appui sur la touche backspace
        $('#modifvolponctuelle_decollage').on('keydown', function(e) {

            var fieldcontent = $('#modifvolponctuelle_decollage').val();

                if (e.keyCode == 8) {
                    laskKeyCodePressed = 8;
                    $('#modifvolponctuelle_decollage').trigger('keypress');
                } else {
                    laskKeyCodePressed = '';
                }
        });




        $('#modifvolponctuelle_atterissage').focus(function (e) {
            $('#modifjournaliereponctuelleEXECUTER').addClass('disabled');
            //$('#modifjournaliereponctuelleANNULER').addClass('disabled');
            $('#modifvolponctuelle_atterissage').val('');
        });
        //event controle saisie de l'heure de décollage
        $('#modifvolponctuelle_atterissage').keypress(function (e) { // password written
            var fieldcontent = $('#modifvolponctuelle_atterissage').val();

            if (fieldcontent.length < 4) {
                if ((parseInt(e.charCode) > 47) && (parseInt(e.charCode) < 58)) {
                    if (fieldcontent.length == 2) {
                        fieldcontent = fieldcontent + ":";
                        $('#modifvolponctuelle_atterissage').val(fieldcontent);
                    }
                } else if ( (laskKeyCodePressed == 8) && (fieldcontent.length == 4) ){
                    $('#modifvolponctuelle_atterissage').val(fieldcontent.slice(0, -1));
                } else {
                    return false;
                }
            } else if (fieldcontent.length == 4) {

                if ((parseInt(e.charCode) > 47) && (parseInt(e.charCode) < 58)) {

                    var test = $('#modifvolponctuelle_atterissage').val()+String.fromCharCode(e.charCode);
                    var temptest = test.split(":");
                    var testhours = parseInt(temptest[0]);
                    var testminutes = parseInt(temptest[1]);

                    if( (testhours<24) && (testminutes<60) ) {
                        if($('input[name=typesaisiehoraire]:checked').val() == 0) {
                            recalcultempsdevol(null, $('#modifvolponctuelle_atterissage').val() + String.fromCharCode(e.charCode));
                        }else{
                            var numberAtt = parseInt( test.replace(":", "") );
                            var test2 = $('#modifvolponctuelle_decollage').val();
                            var numberDec = parseInt( test2.replace(":", "") );

                            if(numberDec<numberAtt) {
                                $('#modifjournaliereponctuelleEXECUTER').removeClass('disabled');
                            }
                        }
                    }else{
                        $('#modifvolponctuelle_atterissage').val('erreur!!!');
                        $('#modifvolponctuelle_avion').trigger('focus');
                        return false; //l'horaire n'est pas valide
                    }

                } else if ( (laskKeyCodePressed == 8) && (fieldcontent.length == 4) ){
                    $('#modifvolponctuelle_decollage').val(fieldcontent.slice(0, -1));
                } else {
                    return false;
                }

            } else {
                return false;
            }
        });
        //detection de l'appui sur la touche backspace
        $('#modifvolponctuelle_atterissage').on('keydown', function(e) {

            var fieldcontent = $('#modifvolponctuelle_atterissage').val();

            if (e.keyCode == 8) {
                laskKeyCodePressed = 8;
                $('#modifvolponctuelle_atterissage').trigger('keypress');
            } else {
                laskKeyCodePressed = '';
            }
        });

        //event modification de l'avion
        $('#modifvolponctuelle_avion').on('change', function() {
            if($('input[name=typesaisiehoraire]:checked').val() == 0) {
                //reset de l'heure de décollage par sa valeur d'origine (à l'ouverture du modal)
                $('#modifvolponctuelle_decollage').val($('#modificationponctuelle_decollageinitial').val());
                //maj du champs temps de demi-tour
                $('#modifvolponctuelle_tempsdemitour').val($('#modifvolponctuelle_avion option[value="' + $('#modifvolponctuelle_avion').val() + '"]').attr("data-demitour-value"));
                //recalcul de l'heure d'arrivée
                recalcultempsdevol($('#modifvolponctuelle_decollage').val(), null);
            }
        });

        //fermeture du modal
        $('#modifjournaliereponctuelleANNULER').on('click', function() {
            $('#myModifJournalierePonctuelleModal').on("hidden.bs.modal", function () {

                $('#modifvolponctuelle_decollage').unbind('change');
                $('#modifvolponctuelle_decollage').unbind('focus');
                $('#modifvolponctuelle_decollage').unbind('keypress');
                $('#modifvolponctuelle_decollage').unbind('keydown');
                $('#modifvolponctuelle_atterissage').unbind('change');
                $('#modifvolponctuelle_atterissage').unbind('focus');
                $('#modifvolponctuelle_atterissage').unbind('keypress');
                $('#modifvolponctuelle_atterissage').unbind('keydown');
                $('#modifvolponctuelle_avion').unbind('change');
                $('#modifjournaliereponctuelleEXECUTER').unbind('click');
                $('#modifjournaliereponctuelleANNULER').unbind('click');

                $('#myModifJournalierePonctuelleHeader').css('display','none');
                $('#myModifJournalierePonctuelleFooter').css('display','none');

            });
        });

        //enregistrement de la modification ponctuelle
        $('#modifjournaliereponctuelleEXECUTER').on('click', function() {

            var changementheuredecollage = "false";
            if( $('#modificationponctuelle_decollageinitial').val() != $('#modifvolponctuelle_decollage').val() ){
                var changementheuredecollage = $('#modifvolponctuelle_decollage').val();
                var changementheureatterissage = $('#modifvolponctuelle_atterissage').val();
            }
            var changementheureatterissage = "false";
            if( $('#modificationponctuelle_atterissageinitial').val() != $('#modifvolponctuelle_atterissage').val() ){
                var changementheureatterissage = $('#modifvolponctuelle_atterissage').val();
                var changementheuredecollage = $('#modifvolponctuelle_decollage').val();
            }

            var url = Routing.generate('planningvol_savemodificationavionvolponctuelle');
            var tableauDeVol = [];
            tableauDeVol.push($('#modifvolponctuelle_idvol').val());
            var tableauDeJour = [];
            tableauDeJour.push($('#modifvolponctuelle_datedebut').val());

            //On enregistre ces modification en BDD
            $.ajax({
                url: url,
                dataType: 'json',
                type: 'POST',
                data: {
                    'arraydatesdelasemaine': tableauDeJour,
                    'arrayvolsid': tableauDeVol,
                    'ancienavionid': $('modifvolponctuelle_idancienavion').val(),
                    'nouvelavionid': $('#modifvolponctuelle_avion').val(),
                    'changementheuredecollage': changementheuredecollage,
                    'changementheureatterissage': changementheureatterissage,
                    'appelMenuContextuel': true
                },
                success: function (data) {

                    $('#myModifJournalierePonctuelleHeader').css('display','none');
                    $('#myModifJournalierePonctuelleFooter').css('display','none');

                    //on recharge la page pour ré-initialiser le calendrier avec les infos valides
                    window.location.reload();

                },
                error: function () {//le serveur a retourné une erreur 500

                    $('#myModifJournalierePonctuelleHeader').css('display','none');
                    $('#myModifJournalierePonctuelleFooter').css('display','none');

                    // l'enregistrement des modifications dans la BDD a retournée une erreur, on affiche
                    bootbox.alert("<i class='glyphicon glyphicon-alert' style='color: red;'></i> Attention: l\'enregistrement des modifications n\'a put se terminer, le serveur a retourné une erreur 500!",
                        function (){

                            //on fais une raz
                            //on efface les selections de jours hebdomadaire
                            $('div[id^="PlHebd_"]').each(function () {
                                $(this).removeClass('joursem_sel');
                                $(this).children().first().next().remove();
                                selectedjoursplanninghebdomadairearray = new Array();
                            });

                            //on vide le tableau la modif viens d'étre effectuee
                            selectedeventsarray = [];

                            //on met a jour la var de session
                            var curentmoment = $('#calendar').fullCalendar('getDate');
                            majPlanningVolParameters(interactionsouris, selectedeventsarray, curentmoment.format('DD-MM-YYYY'), zoom_y, typedemodificationplanning, typedeplanning, periodecustomdebut, periodecustomfin,selectedIdSaison);

                            //on recharge la page pour ré-initialiser les fonctions du modal
                            window.location.reload();
                        });
                }
            });

            //on efface les selections de jours hebdomadaire
            $('div[id^="PlHebd_"]').each(function () {
                $(this).removeClass('joursem_sel');
                $(this).children().first().next().remove();
                selectedjoursplanninghebdomadairearray = new Array();
            });

            //on vide le tableau la modif viens d'étre effectuee
            selectedeventsarray = [];

            //on met a jour la var de session
            var curentmoment = $('#calendar').fullCalendar('getDate');
            majPlanningVolParameters(interactionsouris, selectedeventsarray, curentmoment.format('DD-MM-YYYY'), zoom_y, typedemodificationplanning, typedeplanning, periodecustomdebut, periodecustomfin,selectedIdSaison);

        });

    }

    //---------------------------------
    //     Impression Document PDF
    //---------------------------------

    function updateCurrentGeneratedPageNb(numeropage){
        $('#pagecouranteprinting').html('page='+numeropage);
    }

    $('#radio_jr').click(function() {
        $('#radio_jr').prop("checked",true);
        $('#radio_per').prop("checked",false);
        $('#printdatedebut').val($("#lundiplanninghebdomadairecourant").val());
        $('#printdatefin').val($("#dimancheplanninghebdomadairecourant").val());
        $('#printdatedebut').prop("disabled",true);
        $('#printdatefin').prop("disabled",true);
    });

    $('#radio_per').click(function() {
        $('#printdatedebut').prop("disabled",false);
        $('#printdatefin').prop("disabled",false);
    });

    //petit menu modal dans le formulaire de configuration impresion pdf
    $.contextMenu({

        selector: '#raccourciseoptionsjours',
        autoHide: true,
        trigger: 'hover',
        items: {
            touslesjours: {
                name: "Tous les jours",
                callback: function(key, opt){
                    $('input[id^="chkbx_jsem"]').each( function () {
                        $(this).prop("checked",true);
                    });
                }
            },
            lesjoursdelasemaine: {
                name: "Les jours de la semaine",
                callback: function(key, opt){
                    $('#chkbx_jsem1').prop("checked",true);
                    $('#chkbx_jsem2').prop("checked",true);
                    $('#chkbx_jsem3').prop("checked",true);
                    $('#chkbx_jsem4').prop("checked",true);
                    $('#chkbx_jsem5').prop("checked",true);
                    $('#chkbx_jsem6').prop("checked",false);
                    $('#chkbx_jsem7').prop("checked",false);
                }
            },
            lesweekends: {
                name: "Les Week Ends",
                callback: function(key, opt){
                    $('#chkbx_jsem1').prop("checked",false);
                    $('#chkbx_jsem2').prop("checked",false);
                    $('#chkbx_jsem3').prop("checked",false);
                    $('#chkbx_jsem4').prop("checked",false);
                    $('#chkbx_jsem5').prop("checked",false);
                    $('#chkbx_jsem6').prop("checked",true);
                    $('#chkbx_jsem7').prop("checked",true);
                }
            },
            aucunjours: {
                name: "Aucun jours de la semaime",
                callback: function(key, opt){
                    $('input[id^="chkbx_jsem"]').each( function () {
                        $(this).prop("checked",false);
                    });
                }
            },
        }
    });

    $('#printavionsall').click(function() {
        $('input[id^="chkbx_pavion"]').each( function () {
            $(this).prop("checked",true);
        });
    });

    $('#printavionsnone').click(function() {
        $('input[id^="chkbx_pavion"]').each( function () {
            $(this).prop("checked",false);
        });
    });

    $('#printavionsaircorsica').click(function() {
        var aavionsaircorsica = $('#tousslesavionsaircorsica').val().split('_');
        $('input[id^="chkbx_pavion"]').each( function () {
            if(aavionsaircorsica.indexOf($(this).val())>=0){
                $(this).prop("checked",true);
            }else{
                $(this).prop("checked",false);
            }
        });
    });

    $('#radio_tsav').click(function() {
        $('#blocavionsaselectionner').css('height','3px');
        $('#printavionsall').prop("disabled",true);
        $('#printavionsaircorsica').prop("disabled",true);
        $('#printavionsnone').prop("disabled",true);
    });

    $('#radio_tsavavvol').click(function() {
        $('#blocavionsaselectionner').css('height','3px');
        $('#printavionsall').prop("disabled",true);
        $('#printavionsaircorsica').prop("disabled",true);
        $('#printavionsnone').prop("disabled",true);
    });

    $('#radio_tsavavvoletimmo').click(function() {
        $('#blocavionsaselectionner').css('height','3px');
        $('#printavionsall').prop("disabled",true);
        $('#printavionsaircorsica').prop("disabled",true);
        $('#printavionsnone').prop("disabled",true);
    });

    $('#radio_selav').click(function() {
        $('input[id^="chkbx_pavion"]').each( function () {
            $(this).prop("checked",false);
        });
        $('#blocavionsaselectionner').css('height','130px');
        $('#printavionsall').prop("disabled",false);
        $('#printavionsaircorsica').prop("disabled",false);
        $('#printavionsnone').prop("disabled",false);
    });


        //----------------------------------------
        //    DatePicker Impression début période
        //----------------------------------------
        //Init DatePicker - Impression début période
        function initDatepickerDebutPeriodeImpression() {
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
        }
        initDatepickerDebutPeriodeImpression();

        //-------------------------------------
        //    DatePicker Impression fin période
        //-------------------------------------
        $(".datepickerPrintF").datepicker({
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
                var printperiodeFin = dateText;
                $('#printdatefin').val(dateText);
                $('#radio_jr').prop("checked", false);
                $('#radio_per').prop("checked", true);
                if ($('#printdatedebut').val() == "") {
                    $(".datepickerPrintD").datepicker('setDate', moment(dateText, 'DD-MM-YYYY').format('MM-DD-YYYY'));
                }
            }
        });

        //------------------------------------------------
        //    DatePicker Date début période custom
        //------------------------------------------------
        $(".datepickerCPD").datepicker({
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
                periodecustomdebut = dateText;
                var curentmoment = $('#calendar').fullCalendar('getDate');
                majPlanningVolParameters(interactionsouris, selectedeventsarray, curentmoment.format('DD-MM-YYYY'), zoom_y, typedemodificationplanning, typedeplanning, periodecustomdebut, periodecustomfin, selectedIdSaison);
            }
        });

        //------------------------------------------------
        //      DatePicker Date fin période custom
        //------------------------------------------------
        $(".datepickerCPF").datepicker({
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
            beforeShow: function(input,inst){
                var dt = $(".datepickerCPD").datepicker('getDate');
                return { minDate: dt };
            },
            onSelect: function (dateText, inst) {
                periodecustomfin = dateText;
                var curentmoment = $('#calendar').fullCalendar('getDate');
                majPlanningVolParameters(interactionsouris, selectedeventsarray, curentmoment.format('DD-MM-YYYY'), zoom_y, typedemodificationplanning, typedeplanning, periodecustomdebut, periodecustomfin, selectedIdSaison);
            }
        });

        //------------------------------------------------
        //          DatePicker Date en édition
        //------------------------------------------------

            $(".datepicker1").datepicker({
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

                    if (typedeplanning == 1) {//planning hebdomadaire ouvert

                        var jourdatepicker = moment(dateText, 'DD-MM-YYYY');
                        var jourdebutsemaine = moment($('div[id^="PlHebd_0"]').attr('data-datedecejour'), 'DD-MM-YYYY');
                        var jourfinsemaine = moment($('div[id^="PlHebd_6"]').attr('data-datedecejour'), 'DD-MM-YYYY');

                        if ((jourdatepicker.isSameOrAfter(jourdebutsemaine)) && (jourdatepicker.isSameOrBefore(jourfinsemaine))) {// aujourd'hui est compris dans le planning hebdomadaire ce n'est pas la peine de le recalculer


                            $('#calendar').fullCalendar('gotoDate', moment(dateText, 'DD-MM-YYYY'));
                            //on met a jour la var de session
                            var curentmoment = $('#calendar').fullCalendar('getDate');

                            selectedeventsarray = []; //on vide le tableau la modif viens d'étre effectuee
                            eventbeingdragged = null; //on efface le dernier event qui était selectionné

                            majPlanningVolParameters(interactionsouris, selectedeventsarray, curentmoment.format('DD-MM-YYYY'), zoom_y, typedemodificationplanning, typedeplanning, periodecustomdebut, periodecustomfin, selectedIdSaison);
                            //on deselectionne tous les jour de la semaines du planning hebdomadaire
                            $('div[id^="PlHebd_"]').each(function () {

                                $(this).removeClass('badge');
                                $(this).removeClass('badge-success');
                            });
                            //on deselectionne le jour de la semaine du planning hebdomadaire
                            $("#PlHebd_" + curentmoment.format('e')).addClass('badge');
                            $("#PlHebd_" + curentmoment.format('e')).addClass('badge-success');
                            $(".datepicker1").trigger('blur');


                        } else {

                            //on detruits kes plannings hebdomadaire
                            destroyAllHebdomadaireCaldendars();

                            //on deselectionne tous les jour de la semaines du planning hebdomadaire
                            $('div[id^="PlHebd_"]').each(function () {

                                $(this).removeClass('badge');
                                $(this).removeClass('badge-success');
                            });

                            $("#bloccalendrierhebdomadaire").css("display", "");
                            $('#leplanninghebdomadaire').css('opacity', '0.0');

                            selectedeventsarray = []; //on vide le tableau la modif viens d'étre effectuee
                            eventbeingdragged = null; //on efface le dernier event qui était selectionné

                            //on efface le contenu des infos saisons du planning hebdomadaire
                            $("div[id^='infodebutfinsaison_']").html('&nbsp;');

                            $('#calendar').fullCalendar('gotoDate', moment(dateText, 'DD-MM-YYYY'));

                            //on met a jour la var de session
                            var curentmoment = $('#calendar').fullCalendar('getDate');
                            majPlanningVolParameters(interactionsouris, selectedeventsarray, curentmoment.format('DD-MM-YYYY'), zoom_y, typedemodificationplanning, typedeplanning, periodecustomdebut, periodecustomfin, selectedIdSaison);

                            //on selectionne le jour de la semaine du planning hebdomadaire
                            $("#PlHebd_" + curentmoment.format('e')).addClass('badge');
                            $("#PlHebd_" + curentmoment.format('e')).addClass('badge-success');

                            //on lance l'affichage du résumé de la semaine (preview des planning de lundi à dimanche)
                            createAllHebdomadaireCaldendars(curentmoment);

                            $(".datepicker1").trigger('blur');

                        }

                    } else {

                        $('#calendar').fullCalendar('gotoDate', moment(dateText, 'DD-MM-YYYY'));
                        //on met a jour la var de session
                        var curentmoment = $('#calendar').fullCalendar('getDate');
                        selectedeventsarray = [];
                        majPlanningVolParameters(interactionsouris, selectedeventsarray, curentmoment.format('DD-MM-YYYY'), zoom_y, typedemodificationplanning, typedeplanning, periodecustomdebut, periodecustomfin, selectedIdSaison);
                        //on deselectionne tous les jour de la semaines du planning hebdomadaire
                        $('div[id^="PlHebd_"]').each(function () {

                            $(this).removeClass('badge');
                            $(this).removeClass('badge-success');
                        });
                        //on deselectionne le jour de la semaine du planning hebdomadaire
                        $("#PlHebd_" + curentmoment.format('e')).addClass('badge');
                        $("#PlHebd_" + curentmoment.format('e')).addClass('badge-success');
                        $(".datepicker1").trigger('blur');

                    }

                }
            });



    function resetDatePickerWhenExitingAjaxLoadedModal(){

        //on efface le ui-datepicker-div créé par le modal qui pose problème
        $("#ui-datepicker-div").remove();

        //on re-initialise le date picker TravailSur pour que datepicker recrée l'element ui-datepicker-div dans le DOM
        initDatepickerDebutPeriodeImpression();

    }

    //--------------------------------------------------------
    //              Evenement
    // Modification de la valeur du select des périodes saison
    //--------------------------------------------------------

    $("#saisonselect1").on("change",function(){

        selectedIdSaison = $(this).find(":selected").val();

        if(typedeplanning == 1) {//planning hebdomadaire ouvert


            var jourdebutsaison = moment($(this).find(":selected").attr('data-datedebutperiodesaison'), 'YYYY-MM-DD HH-mm-ss');
            var jourdebutsemaine = moment($('div[id^="PlHebd_0"]').attr('data-datedecejour'), 'DD-MM-YYYY');
            var jourfinsemaine = moment($('div[id^="PlHebd_6"]').attr('data-datedecejour'), 'DD-MM-YYYY');

            if ((jourdebutsaison.isSameOrAfter(jourdebutsemaine)) && (jourdebutsaison.isSameOrBefore(jourfinsemaine))) {// le debut de la saison est compris dans le planning hebdomadaire ce n'est pas la peine de le recalculer

                $('#calendar').fullCalendar('gotoDate', moment($(this).find(":selected").attr('data-datedebutperiodesaison'), 'YYYY-MM-DD HH-mm-ss'));

                //on met a jour la var de session
                var curentmoment = $('#calendar').fullCalendar('getDate');

                selectedeventsarray = [];
                majPlanningVolParameters(interactionsouris, selectedeventsarray, curentmoment.format('DD-MM-YYYY'), zoom_y, typedemodificationplanning, typedeplanning,periodecustomdebut,periodecustomfin,selectedIdSaison);

                //on deselectionne tous les jour de la semaines du planning hebdomadaire
                $('div[id^="PlHebd_"]').each(function () {

                    $(this).removeClass('badge');
                    $(this).removeClass('badge-success');
                });

                //on selectionne le jour de la semaine du planning hebdomadaire
                $("#PlHebd_" + curentmoment.format('e')).addClass('badge');
                $("#PlHebd_" + curentmoment.format('e')).addClass('badge-success');


            }else{

                //on efface le contenu des infos saisons du planning hebdomadaire
                $("div[id^='infodebutfinsaison_']").html('&nbsp;');

                //on detruits kes plannings hebdomadaire
                destroyAllHebdomadaireCaldendars();

                //on deselectionne tous les jour de la semaines du planning hebdomadaire
                $('div[id^="PlHebd_"]').each(function(){

                    $(this).removeClass('badge');
                    $(this).removeClass('badge-success');
                });

                $("#bloccalendrierhebdomadaire").css("display","");
                $('#leplanninghebdomadaire').css('opacity','0.0');
                selectedeventsarray = [];

                $('#calendar').fullCalendar('gotoDate', moment($(this).find(":selected").attr('data-datedebutperiodesaison'), 'YYYY-MM-DD HH-mm-ss'));

                //on met a jour la var de session
                var curentmoment = $('#calendar').fullCalendar('getDate');
                majPlanningVolParameters(interactionsouris,selectedeventsarray,curentmoment.format('DD-MM-YYYY'),zoom_y,typedemodificationplanning,typedeplanning,periodecustomdebut,periodecustomfin,selectedIdSaison);

                //on selectionne le jour de la semaine du planning hebdomadaire
                $("#PlHebd_"+curentmoment.format('e')).addClass('badge');
                $("#PlHebd_"+curentmoment.format('e')).addClass('badge-success');

                //on lance l'affichage du résumé de la semaine (preview des planning de lundi à dimanche)
                createAllHebdomadaireCaldendars(curentmoment);

            }

        }else {


            $('#calendar').fullCalendar('gotoDate', moment($(this).find(":selected").attr('data-datedebutperiodesaison'), 'YYYY-MM-DD HH-mm-ss'));

            //on met a jour la var de session
            var curentmoment = $('#calendar').fullCalendar('getDate');

            selectedeventsarray = [];
            majPlanningVolParameters(interactionsouris, selectedeventsarray, curentmoment.format('DD-MM-YYYY'), zoom_y, typedemodificationplanning, typedeplanning,periodecustomdebut,periodecustomfin,selectedIdSaison);

            //on deselectionne tous les jour de la semaines du planning hebdomadaire
            $('div[id^="PlHebd_"]').each(function () {

                $(this).removeClass('badge');
                $(this).removeClass('badge-success');
            });

            //on selectionne le jour de la semaine du planning hebdomadaire
            $("#PlHebd_" + curentmoment.format('e')).addClass('badge');
            $("#PlHebd_" + curentmoment.format('e')).addClass('badge-success');


        }


    });


    //--------------------------------------------------------------
    //              Evenement
    // Modification de la valeur du select des templates de travail
    //-------------------------------------------------------------

    $("#templateselect2").on("change",function(){

        //on récupére l'ide selectionné
        var id = $(this).val();

        var url = Routing.generate('planningvol_settemplatechangesessionvar');
        //on modifie la variable de session du templatecourant
        $.ajax({
            url: url,
            dataType: 'json',
            type: 'POST',
            data: {
                'nouveautemplateselectionneid': id
            },
            success: function(doc) {

                //on efface le calendrier
                $('#calendar').fullCalendar( 'removeEvents');
                //on recharge le calendrier avec les vols du nouveau template
                $('#calendar').fullCalendar( 'refetchEvents' );

            },
            error: function(){
            }
        });


    });
    // =========================================================================================================================
    //                         paramètres  FullCalendar (Planning principal et Plannings Hebdomadaire)
    // =========================================================================================================================

    //-----------------------------------------------
    //variables petits planning preview hebdomadaire
    //-----------------------------------------------

    var loadpage = true;
    var lesresources; //le tableau de resources au format fullcalendar récupéré lors du chargement du calendrier principal (on se sert de sa valeur pour les preview de planning hebdomadaire)
    var leseventsdujourcourant; //le tableau de resources du jour courant au format fullcalendar récupéré du calendrier principal pour ce jour de la semaine des preview du planning
    var largeurDesColonnesPreviewHebdomadaire = 10;
    var hauteurDuContenuDesCellulesResumeHebdomadaire = 13; //20;
    var hauteurCellulesPreviewHebdomadaire = 10;//17; //3 pixels de moins que la hauteurDuContenuDesCellulesResumeHebdomadaire
    var nbHebdoPreviewDaysDataLoaded = 0;
    var typedeplanning = parseInt($("#parametresutilisationplanningvol").attr('data-typedeplanning'));

    //-----------------------------------------
    //variables planning principal jour courant
    //-----------------------------------------

    var affichageDesTooltipsAidePourBoutonsInterface = true;
    var interdictionDragStart = false;
    var libelleErreurInterdictionDragStart = "";
    var couleursAlternativeFondPlanningJoursFuturs = ["#dfdfdf","#d7d7d7"];
    var couleursAlternativeFondPlanningJoursWeekend = ["#ffafaf","#f89898"];
    var couleursAlternativeFondPlanningJoursPasses = ["#dfdfdf","#d7d7d7"];
    var couleursAlternativeFondAvionImmobilise = ["#ee4444","#cc2222"];
    var couleurDeSelectionDesVols = "#000000";
    var couleurDeSelectionDesJoursSemaine = "#000000";
    var couleurDeFondCellulesAvions_Affrete = "#ed4256"; //orange
    var couleurDeFondCellulesAvions_XK_AT7 = "#41b5ee"; //bleu
    var couleurDeFondCellulesAvions_XK_320 = "#4267ed"; //violet
    var couleurTexteCellulesAvions = "#ffffff";
    var couleurInfoTexteDebutFinSaison = "#0071aa";
    var couleurTexteVolsAvecMsgEnvoye = "#4b722D"; //vert fonçé
    var couleurTexteVolsAvecMsgNonEnvoye = "#3782BF"; //bleu fonçé
    var affichageplanningavant4h;
    var firsttimelineplanningrendering = false;
    var eventbeingdragged = null; //le vol en train d'étre déplacé
    var startresourceeventbeingdragged = null; //l'avion effectuant le vol en train d'étre déplacé
    var todaydate = new Date();
    var todaydateText = todaydate.getDate()+"-"+("0" + (todaydate.getMonth()+1)).slice(-2)+"-"+todaydate.getFullYear();
    var defaultEventWidth=75;
    var defaultEventHeight=30;
    var zoom_x_default = 3; //(0 mini -> 10 max)
    var zoom_x=zoom_x_default;
    var zoom_y_default = 8; //(0 mini -> 10 max
    var zoom_y_mini = 5;
    var zoom_y=zoom_y_default;
    var typedemodificationplanning = $("#parametresutilisationplanningvol").attr('data-typedemodificationplanning'); //ponctuelle = 0 / Saison = 1 / Période custom = 2
    var interactionsouris = ($("#parametresutilisationplanningvol").attr('data-interactionsourisplanning') === 'true'); //interdit/autorise les déplacement à la souris des vols et les selections des vols
    var selectedeventsarray = $("#parametresutilisationplanningvol").attr('data-aselectionvolsplanning');
    var selectedIdSaison = $("#parametresutilisationplanningvol").attr('data-selectedIdSaison');
    if(selectedeventsarray == "null"){
        selectedeventsarray = new Array();
    }else{
        selectedeventsarray = $.parseJSON(selectedeventsarray);
    }
    var selectedjoursplanninghebdomadairearray = new Array();
    var m = moment($("#parametresutilisationplanningvol").attr('data-dateplanningencouredition'),"DD-MM-YYYY");
    if($("#parametresutilisationplanningvol").attr('data-zoominterface') != '-1'){

        zoom_y = parseInt($("#parametresutilisationplanningvol").attr('data-zoominterface'));
    }
    var timelinesechellevisualisation = 1;
    var userisgranted;
    if($("#parametresutilisationplanningvol").attr('data-userisgranted') == 'true'){
        userisgranted = true;
    }else{
        userisgranted = false;
        interactionsouris = false;
        typedemodificationplanning = 0;
    }
    var periodecustomdebut = $("#periodecustomdatedebut").val();
    var periodecustomfin = $("#periodecustomdatefin").val();
    var aJQueryElementsVolsAvecUnAvionImmobilise = new Array(); //pour représenter les vol d'essais sur un avions immobilisé


    // =========================================================================================================================
    //                                              FullCalendar Journalier
    // =========================================================================================================================

    $('#calendar').fullCalendar({
        locale: 'fr',
        schedulerLicenseKey: 'GPL-My-Project-Is-Open-Source',
        now: m,
        editable: interactionsouris, // disable draggable events
        height: "auto",
        scrollTime: '00:00', // undo default 6am scrollTime

        resourceAreaWidth:120,
        handleWindowResize: true,
        titleFormat: 'ddd DD MMM YYYY', //format de la date
        eventOverlap: false, //empeche le chevauchement de 2 events
        slotWidth: 75, //Width of each slot in pixels
        slotDuration: '01:00', //The length of time each vertical line of the timeline represents. Without this option, a reasonable value will be automatically computed based on the view's total duration. (1 heure)
        snapDuration: '99:00', //On bloque le déplacement vertical en mettant un interval géant
        minTime: '04:00', //Determines the starting time that will be displayed, even when the scrollbars have been scrolled all the way up.
        maxTime: '24:00', //Determines the end time (exclusively) that will be displayed, even when the scrollbars have been scrolled all the way down.
        header: {
            left: 'title',
            center: 'timeline24hCustomButton,timeline20hCustomButton,   journalierCustomButton,hebdomadaireCustomButton,   interactionOnOffCustomButton,selectionEraserCustomButton,   zoomMoinsCustomButton,zoomDefaultCustomButton,zoomPlusCustomButton,   enlargeShrinkCustomButton,   refreshCustomButton,   printCustomButton',
            right: 'todayCustomButton previousMonthCustomButton,weekLessCustomButton,prevCustomButton,nextCustomButton,weekMoreCustomButton,nextMonthCustomButton'
        },
        windowResize: function (view) {

            //oblige les lignes d'avion à avoir toujours la même hauteur, même en cas d'enregistrement de vol éroné (même heure)
            $("#calendar .fc-content table tbody tr td").children().css("height",eval(Math.floor(defaultEventHeight+((defaultEventHeight*zoom_y)/10))-4)+"px");

        },
        customButtons: {

            selectionEraserCustomButton: {
                text: '',
                click: function() {

                    //on vide le tableau la modif viens d'étre effectuee
                    selectedeventsarray = [];

                    //on efface le dernier event qui était selectionné
                    eventbeingdragged = null;

                    if(typedeplanning == 1) { //on se trouve en mode hebdomadaire
                        // on efface les icone L des jours du planning hebdomadaire selectionné
                        $('div[id^="PlHebd_"]').each(function () {
                            $(this).removeClass('joursem_sel');
                            $(this).children().first().next().remove();
                            selectedjoursplanninghebdomadairearray = new Array();
                        });
                    }

                    //on regénére les events du planning
                    $('#calendar').fullCalendar( 'rerenderEvents' );

                    //on met a jour la var de session
                    var curentmoment = $('#calendar').fullCalendar('getDate');
                    majPlanningVolParameters(interactionsouris,selectedeventsarray,curentmoment.format('DD-MM-YYYY'),zoom_y,typedemodificationplanning,typedeplanning,periodecustomdebut,periodecustomfin,selectedIdSaison);

                }
            },
            todayCustomButton: {
                text: 'Aujourd\'hui',
                click: function() {

                    if(typedeplanning == 1) {//planning hebdomadaire ouvert

                        var jouraujourdhui = moment();
                        var jourdebutsemaine= moment($('div[id^="PlHebd_0"]').attr('data-datedecejour'),'DD-MM-YYYY');
                        var jourfinsemaine= moment($('div[id^="PlHebd_6"]').attr('data-datedecejour'),'DD-MM-YYYY');

                        if( (jouraujourdhui.isSameOrAfter(jourdebutsemaine)) && (jouraujourdhui.isSameOrBefore(jourfinsemaine)) ){// aujourd'hui est compris dans le planning hebdomadaire ce n'est pas la peine de le recalculer

                            todaydate = new Date();
                            $('#calendar').fullCalendar('gotoDate', todaydate);

                            selectedeventsarray = []; //on vide le tableau la modif viens d'étre effectuee
                            eventbeingdragged = null; //on efface le dernier event qui était selectionné

                            //on met a jour la var de session
                            var curentmoment = $('#calendar').fullCalendar('getDate');
                            majPlanningVolParameters(interactionsouris, selectedeventsarray, curentmoment.format('DD-MM-YYYY'), zoom_y, typedemodificationplanning, typedeplanning,periodecustomdebut,periodecustomfin,selectedIdSaison);

                            firsttimelineplanningrendering = false; //on permet la mise a jour de la timeline

                            //on deselectionne tous les jour de la semaines du planning hebdomadaire
                            $('div[id^="PlHebd_"]').each(function () {

                                $(this).removeClass('badge');
                                $(this).removeClass('badge-success');

                            });

                            //on selectionne le jour de la semaine du planning hebdomadaire
                            $("#PlHebd_" + curentmoment.format('e')).addClass('badge');
                            $("#PlHebd_" + curentmoment.format('e')).addClass('badge-success');


                        }else{

                            //on detruits kes plannings hebdomadaire
                            destroyAllHebdomadaireCaldendars();

                            //on deselectionne tous les jour de la semaines du planning hebdomadaire
                            $('div[id^="PlHebd_"]').each(function(){
                                //$(this).css("color","");
                                $(this).removeClass('badge');
                                $(this).removeClass('badge-success');
                            });

                            $("#bloccalendrierhebdomadaire").css("display","");
                            $('#leplanninghebdomadaire').css('opacity','0.0');

                            selectedeventsarray = []; //on vide le tableau la modif viens d'étre effectuee
                            eventbeingdragged = null; //on efface le dernier event qui était selectionné

                            //on efface le contenu des infos saisons du planning hebdomadaire
                            $("div[id^='infodebutfinsaison_']").html('&nbsp;');

                            todaydate = new Date();
                            $('#calendar').fullCalendar('gotoDate', todaydate);

                            //on met a jour la var de session
                            var curentmoment = $('#calendar').fullCalendar('getDate');
                            majPlanningVolParameters(interactionsouris,selectedeventsarray,curentmoment.format('DD-MM-YYYY'),zoom_y,typedemodificationplanning,typedeplanning,periodecustomdebut,periodecustomfin,selectedIdSaison);

                            //on selectionne le jour de la semaine du planning hebdomadaire
                            $("#PlHebd_"+curentmoment.format('e')).addClass('badge');
                            $("#PlHebd_"+curentmoment.format('e')).addClass('badge-success');

                            //on lance l'affichage du résumé de la semaine (preview des planning de lundi à dimanche)
                            createAllHebdomadaireCaldendars(curentmoment);


                        }

                    }else {

                        todaydate = new Date();
                        $('#calendar').fullCalendar('gotoDate', todaydate);

                        //on met a jour la var de session
                        var curentmoment = $('#calendar').fullCalendar('getDate');
                        majPlanningVolParameters(interactionsouris, selectedeventsarray, curentmoment.format('DD-MM-YYYY'), zoom_y, typedemodificationplanning, typedeplanning,periodecustomdebut,periodecustomfin,selectedIdSaison);

                        firsttimelineplanningrendering = false; //on permet la mise a jour de la timeline

                        //on deselectionne tous les jour de la semaines du planning hebdomadaire
                        $('div[id^="PlHebd_"]').each(function () {

                            $(this).removeClass('badge');
                            $(this).removeClass('badge-success');

                        });

                        //on selectionne le jour de la semaine du planning hebdomadaire
                        $("#PlHebd_" + curentmoment.format('e')).addClass('badge');
                        $("#PlHebd_" + curentmoment.format('e')).addClass('badge-success');

                    }
                }
            },
            prevCustomButton: {
                text: '',
                click: function() {

                    var curentmoment = $('#calendar').fullCalendar('getDate');
                    if(curentmoment.format('e') != 0 ) {//si le jour courant n'est pas lundi


                        $('#calendar').fullCalendar('prev');
                        var curentmoment = $('#calendar').fullCalendar('getDate');

                        selectedeventsarray = []; //on vide le tableau la modif viens d'étre effectuee
                        eventbeingdragged = null; //on efface le dernier event qui était selectionné

                        //on met a jour la var de session
                        majPlanningVolParameters(interactionsouris, selectedeventsarray, curentmoment.format('DD-MM-YYYY'), zoom_y, typedemodificationplanning, typedeplanning,periodecustomdebut,periodecustomfin,selectedIdSaison);
                        firsttimelineplanningrendering = false; //on permet la mise a jour de la timeline

                        //on deselectionne tous les jour de la semaines du planning hebdomadaire
                        $('div[id^="PlHebd_"]').each(function () {

                            $(this).removeClass('badge');
                            $(this).removeClass('badge-success');

                        });

                        //on selectionne le jour de la semaine du planning hebdomadaire
                        $("#PlHebd_" + curentmoment.format('e')).addClass('badge');
                        $("#PlHebd_" + curentmoment.format('e')).addClass('badge-success');

                    }else{//le jour precedent sera un dimanche, il faut vérifier que le planning hebdomadaire n'est pas ouvert sinon on le recalcul

                        if(typedeplanning == 1) {//planning hebdomadaire ouvert

                            //on detruits kes plannings hebdomadaire
                            destroyAllHebdomadaireCaldendars();

                            //on deselectionne tous les jour de la semaines du planning hebdomadaire
                            $('div[id^="PlHebd_"]').each(function(){
                                //$(this).css("color","");
                                $(this).removeClass('badge');
                                $(this).removeClass('badge-success');
                            });

                            $("#bloccalendrierhebdomadaire").css("display","");
                            $('#leplanninghebdomadaire').css('opacity','0.0');

                            selectedeventsarray = []; //on vide le tableau la modif viens d'étre effectuee
                            eventbeingdragged = null; //on efface le dernier event qui était selectionné

                            //on efface le contenu des infos saisons du planning hebdomadaire
                            $("div[id^='infodebutfinsaison_']").html('&nbsp;');

                            $('#calendar').fullCalendar('prev');
                            var curentmoment = $('#calendar').fullCalendar('getDate');

                            //on selectionne le jour de la semaine du planning hebdomadaire
                            $("#PlHebd_"+curentmoment.format('e')).addClass('badge');
                            $("#PlHebd_"+curentmoment.format('e')).addClass('badge-success');

                            //on met a jour la var de session
                            var curentmoment = $('#calendar').fullCalendar('getDate');
                            majPlanningVolParameters(interactionsouris,selectedeventsarray,curentmoment.format('DD-MM-YYYY'),zoom_y,typedemodificationplanning,typedeplanning,periodecustomdebut,periodecustomfin,selectedIdSaison);

                            //on lance l'affichage du résumé de la semaine (preview des planning de lundi à dimanche)
                            createAllHebdomadaireCaldendars(curentmoment);

                        }else{

                            $('#calendar').fullCalendar('prev');
                            var curentmoment = $('#calendar').fullCalendar('getDate');

                            selectedeventsarray = []; //on vide le tableau la modif viens d'étre effectuee
                            eventbeingdragged = null; //on efface le dernier event qui était selectionné

                            //on met a jour la var de session
                            majPlanningVolParameters(interactionsouris, selectedeventsarray, curentmoment.format('DD-MM-YYYY'), zoom_y, typedemodificationplanning, typedeplanning,periodecustomdebut,periodecustomfin,selectedIdSaison);
                            firsttimelineplanningrendering = false; //on permet la mise a jour de la timeline

                            //on deselectionne tous les jour de la semaines du planning hebdomadaire
                            $('div[id^="PlHebd_"]').each(function () {

                                $(this).removeClass('badge');
                                $(this).removeClass('badge-success');

                            });

                            //on selectionne le jour de la semaine du planning hebdomadaire
                            $("#PlHebd_" + curentmoment.format('e')).addClass('badge');
                            $("#PlHebd_" + curentmoment.format('e')).addClass('badge-success');

                        }

                    }


                }
            },
            nextMonthCustomButton: {
                text: '',
                click: function () {

                    var curentmoment = $('#calendar').fullCalendar('getDate');

                    if(typedeplanning == 1) {//planning hebdomadaire ouvert

                        //on detruits kes plannings hebdomadaire
                        destroyAllHebdomadaireCaldendars();

                        //on deselectionne tous les jour de la semaines du planning hebdomadaire
                        $('div[id^="PlHebd_"]').each(function(){

                            $(this).removeClass('badge');
                            $(this).removeClass('badge-success');
                        });

                        $("#bloccalendrierhebdomadaire").css("display","");
                        $('#leplanninghebdomadaire').css('opacity','0.0');

                        selectedeventsarray = []; //on vide le tableau la modif viens d'étre effectuee
                        eventbeingdragged = null; //on efface le dernier event qui était selectionné

                        //on efface le contenu des infos saisons du planning hebdomadaire
                        $("div[id^='infodebutfinsaison_']").html('&nbsp;');

                        var curentmoment = $('#calendar').fullCalendar('getDate');
                        var updatemoment = curentmoment.add(1, 'months');
                        $('#calendar').fullCalendar('gotoDate', updatemoment);
                        curentmoment = $('#calendar').fullCalendar('getDate');

                        //on selectionne le jour de la semaine du planning hebdomadaire
                        $("#PlHebd_"+curentmoment.format('e')).addClass('badge');
                        $("#PlHebd_"+curentmoment.format('e')).addClass('badge-success');

                        //on met a jour la var de session
                        var curentmoment = $('#calendar').fullCalendar('getDate');
                        majPlanningVolParameters(interactionsouris,selectedeventsarray,curentmoment.format('DD-MM-YYYY'),zoom_y,typedemodificationplanning,typedeplanning,periodecustomdebut,periodecustomfin,selectedIdSaison);

                        //on lance l'affichage du résumé de la semaine (preview des planning de lundi à dimanche)
                        createAllHebdomadaireCaldendars(curentmoment);

                    }else{

                        var curentmoment = $('#calendar').fullCalendar('getDate');
                        var updatemoment = curentmoment.add(1, 'months');
                        $('#calendar').fullCalendar('gotoDate', updatemoment);
                        curentmoment = $('#calendar').fullCalendar('getDate');

                        selectedeventsarray = []; //on vide le tableau la modif viens d'étre effectuee
                        eventbeingdragged = null; //on efface le dernier event qui était selectionné

                        //on met a jour la var de session
                        majPlanningVolParameters(interactionsouris, selectedeventsarray, updatemoment.format('DD-MM-YYYY'), zoom_y, typedemodificationplanning, typedeplanning,periodecustomdebut,periodecustomfin,selectedIdSaison);
                        firsttimelineplanningrendering = false; //on permet la mise a jour de la timeline

                        //on deselectionne tous les jour de la semaines du planning hebdomadaire
                        $('div[id^="PlHebd_"]').each(function () {

                            $(this).removeClass('badge');
                            $(this).removeClass('badge-success');

                        });

                        //on selectionne le jour de la semaine du planning hebdomadaire
                        $("#PlHebd_" + updatemoment.format('e')).addClass('badge');
                        $("#PlHebd_" + updatemoment.format('e')).addClass('badge-success');

                    }

                }
            },
            previousMonthCustomButton: {
                text: '',
                click: function () {

                    var curentmoment = $('#calendar').fullCalendar('getDate');

                    if(typedeplanning == 1) {//planning hebdomadaire ouvert

                        //on detruits kes plannings hebdomadaire
                        destroyAllHebdomadaireCaldendars();

                        //on deselectionne tous les jour de la semaines du planning hebdomadaire
                        $('div[id^="PlHebd_"]').each(function(){
                            $(this).removeClass('badge');
                            $(this).removeClass('badge-success');
                        });

                        $("#bloccalendrierhebdomadaire").css("display","");
                        $('#leplanninghebdomadaire').css('opacity','0.0');

                        selectedeventsarray = []; //on vide le tableau la modif viens d'étre effectuee
                        eventbeingdragged = null; //on efface le dernier event qui était selectionné

                        //on efface le contenu des infos saisons du planning hebdomadaire
                        $("div[id^='infodebutfinsaison_']").html('&nbsp;');

                        var curentmoment = $('#calendar').fullCalendar('getDate');
                        var updatemoment = curentmoment.subtract(1, 'months');
                        $('#calendar').fullCalendar('gotoDate', updatemoment);
                        curentmoment = $('#calendar').fullCalendar('getDate');

                        //on selectionne le jour de la semaine du planning hebdomadaire
                        $("#PlHebd_"+curentmoment.format('e')).addClass('badge');
                        $("#PlHebd_"+curentmoment.format('e')).addClass('badge-success');

                        //on met a jour la var de session
                        var curentmoment = $('#calendar').fullCalendar('getDate');
                        majPlanningVolParameters(interactionsouris,selectedeventsarray,curentmoment.format('DD-MM-YYYY'),zoom_y,typedemodificationplanning,typedeplanning,periodecustomdebut,periodecustomfin,selectedIdSaison);

                        //on lance l'affichage du résumé de la semaine (preview des planning de lundi à dimanche)
                        createAllHebdomadaireCaldendars(curentmoment);

                    }else{

                        var curentmoment = $('#calendar').fullCalendar('getDate');
                        var updatemoment = curentmoment.subtract(1, 'months');
                        $('#calendar').fullCalendar('gotoDate', updatemoment);
                        curentmoment = $('#calendar').fullCalendar('getDate');

                        selectedeventsarray = []; //on vide le tableau la modif viens d'étre effectuee
                        eventbeingdragged = null; //on efface le dernier event qui était selectionné

                        //on met a jour la var de session
                        majPlanningVolParameters(interactionsouris, selectedeventsarray, updatemoment.format('DD-MM-YYYY'), zoom_y, typedemodificationplanning, typedeplanning,periodecustomdebut,periodecustomfin,selectedIdSaison);
                        firsttimelineplanningrendering = false; //on permet la mise a jour de la timeline

                        //on deselectionne tous les jour de la semaines du planning hebdomadaire
                        $('div[id^="PlHebd_"]').each(function () {

                            $(this).removeClass('badge');
                            $(this).removeClass('badge-success');

                        });

                        //on deselectionne le jour de la semaine du planning hebdomadaire
                        $("#PlHebd_" + updatemoment.format('e')).addClass('badge');
                        $("#PlHebd_" + updatemoment.format('e')).addClass('badge-success');

                    }

                }
            },
            nextCustomButton: {
                text: '',
                click: function() {

                    var curentmoment = $('#calendar').fullCalendar('getDate');
                    if(curentmoment.format('e') != 6 ) {// si le jour courant n'est pas dimanche

                        $('#calendar').fullCalendar('next');
                        var curentmoment = $('#calendar').fullCalendar('getDate');

                        selectedeventsarray = []; //on vide le tableau la modif viens d'étre effectuee
                        eventbeingdragged = null; //on efface le dernier event qui était selectionné

                        //on met a jour la var de session
                        majPlanningVolParameters(interactionsouris,selectedeventsarray,curentmoment.format('DD-MM-YYYY'),zoom_y,typedemodificationplanning,typedeplanning,periodecustomdebut,periodecustomfin,selectedIdSaison);

                        firsttimelineplanningrendering = false; //on permet la mise a jour de la timeline
                        //on deselectionne tous les jour de la semaines du planning hebdomadaire
                        $('div[id^="PlHebd_"]').each(function(){

                            $(this).removeClass('badge');
                            $(this).removeClass('badge-success');

                        });

                        //on selectionne le jour de la semaine du planning hebdomadaire
                        //$("#PlHebd_"+curentmoment.format('e')).css("color",couleurdeselectionjourdelasemaine);
                        $("#PlHebd_"+curentmoment.format('e')).addClass('badge');
                        $("#PlHebd_"+curentmoment.format('e')).addClass('badge-success');

                    }else{//le jour suivant sera un lundi, il faut vérifier que le planning hebdomadaire n'est pas ouvert sinon on le recalcul

                        if(typedeplanning == 1){//planning hebdomadaire ouvert

                            //on detruits kes plannings hebdomadaire
                            destroyAllHebdomadaireCaldendars();

                            //on deselectionne tous les jour de la semaines du planning hebdomadaire
                            $('div[id^="PlHebd_"]').each(function(){
                                //$(this).css("color","");
                                $(this).removeClass('badge');
                                $(this).removeClass('badge-success');
                            });

                            $("#bloccalendrierhebdomadaire").css("display","");
                            $('#leplanninghebdomadaire').css('opacity','0.0');

                            selectedeventsarray = []; //on vide le tableau la modif viens d'étre effectuee
                            eventbeingdragged = null; //on efface le dernier event qui était selectionné

                            //on efface le contenu des infos saisons du planning hebdomadaire
                            $("div[id^='infodebutfinsaison_']").html('&nbsp;');

                            $('#calendar').fullCalendar('next');
                            var curentmoment = $('#calendar').fullCalendar('getDate');

                            //on selectionne le jour de la semaine du planning hebdomadaire
                            $("#PlHebd_"+curentmoment.format('e')).addClass('badge');
                            $("#PlHebd_"+curentmoment.format('e')).addClass('badge-success');

                            //on met a jour la var de session
                            var curentmoment = $('#calendar').fullCalendar('getDate');
                            majPlanningVolParameters(interactionsouris,selectedeventsarray,curentmoment.format('DD-MM-YYYY'),zoom_y,typedemodificationplanning,typedeplanning,periodecustomdebut,periodecustomfin,selectedIdSaison);

                            //on lance l'affichage du résumé de la semaine (preview des planning de lundi à dimanche)
                            createAllHebdomadaireCaldendars(curentmoment);

                        }else{

                            $('#calendar').fullCalendar('next');
                            var curentmoment = $('#calendar').fullCalendar('getDate');

                            selectedeventsarray = []; //on vide le tableau la modif viens d'étre effectuee
                            eventbeingdragged = null; //on efface le dernier event qui était selectionné

                            //on met a jour la var de session
                            majPlanningVolParameters(interactionsouris,selectedeventsarray,curentmoment.format('DD-MM-YYYY'),zoom_y,typedemodificationplanning,typedeplanning,periodecustomdebut,periodecustomfin,selectedIdSaison);

                            firsttimelineplanningrendering = false; //on permet la mise a jour de la timeline
                            //on deselectionne tous les jour de la semaines du planning hebdomadaire
                            $('div[id^="PlHebd_"]').each(function(){

                                //$(this).css("color","");
                                $(this).removeClass('badge');
                                $(this).removeClass('badge-success');

                            });

                            //on selectionne le jour de la semaine du planning hebdomadaire
                            $("#PlHebd_"+curentmoment.format('e')).addClass('badge');
                            $("#PlHebd_"+curentmoment.format('e')).addClass('badge-success');

                        }
                    }

                }
            },
            refreshCustomButton: {
                text: '',
                click: function() {

                    //reset zoom Y (par defaut)
                    zoom_y=zoom_y_default;

                    //reset des seletction
                    selectedeventsarray = []; //on vide le tableau la modif viens d'étre effectuee
                    eventbeingdragged = null; //on efface le dernier event qui était selectionné

                    //selection souris à OFF (par defaut)
                    interactionsouris = false;

                    //période custom à zero (par defaut)
                    periodecustomdebut = "";
                    periodecustomfin = "";

                    //reset choix modification à ponctuel (par defaut)
                    typedemodificationplanning = 0;

                    //reset timeline à 04h-23h
                    timelinesechellevisualisation = 1;
                    firsttimelineplanningrendering = true;

                    //reset choix planning à journalier (par defaut)
                    typedeplanning = 0;

                    //on efface le contenu des infos saisons du planning hebdomadaire
                    $("div[id^='infodebutfinsaison_']").html('&nbsp;');

                    //on met a jour la var de session
                    var curentmoment = $('#calendar').fullCalendar('getDate');

                    majPlanningVolParameters(interactionsouris, selectedeventsarray, curentmoment.format('DD-MM-YYYY'), zoom_y, typedemodificationplanning, typedeplanning,periodecustomdebut,periodecustomfin,selectedIdSaison);

                    //on recharge la page
                    window.location.reload();

                }
            },
            weekMoreCustomButton: {
                text: '',
                click: function() {

                    var curentmoment = $('#calendar').fullCalendar('getDate');

                    if(typedeplanning == 1) {//planning hebdomadaire ouvert

                        //on detruits kes plannings hebdomadaire
                        destroyAllHebdomadaireCaldendars();

                        //on deselectionne tous les jour de la semaines du planning hebdomadaire
                        $('div[id^="PlHebd_"]').each(function(){
                            $(this).removeClass('badge');
                            $(this).removeClass('badge-success');
                        });

                        $("#bloccalendrierhebdomadaire").css("display","");
                        $('#leplanninghebdomadaire').css('opacity','0.0');

                        selectedeventsarray = []; //on vide le tableau la modif viens d'étre effectuee
                        eventbeingdragged = null; //on efface le dernier event qui était selectionné

                        //on efface le contenu des infos saisons du planning hebdomadaire
                        $("div[id^='infodebutfinsaison_']").html('&nbsp;');

                        var curentmoment = $('#calendar').fullCalendar('getDate');
                        var updatemoment = curentmoment.add(7, 'days');
                        $('#calendar').fullCalendar('gotoDate', updatemoment);
                        curentmoment = $('#calendar').fullCalendar('getDate');

                        //on selectionne le jour de la semaine du planning hebdomadaire
                        $("#PlHebd_"+curentmoment.format('e')).addClass('badge');
                        $("#PlHebd_"+curentmoment.format('e')).addClass('badge-success');

                        //on met a jour la var de session
                        var curentmoment = $('#calendar').fullCalendar('getDate');
                        majPlanningVolParameters(interactionsouris,selectedeventsarray,curentmoment.format('DD-MM-YYYY'),zoom_y,typedemodificationplanning,typedeplanning,periodecustomdebut,periodecustomfin,selectedIdSaison);

                        //on lance l'affichage du résumé de la semaine (preview des planning de lundi à dimanche)
                        createAllHebdomadaireCaldendars(curentmoment);


                    }else {

                        var curentmoment = $('#calendar').fullCalendar('getDate');
                        var updatemoment = curentmoment.add(7, 'days');
                        $('#calendar').fullCalendar('gotoDate', updatemoment);
                        curentmoment = $('#calendar').fullCalendar('getDate');

                        selectedeventsarray = []; //on vide le tableau la modif viens d'étre effectuee
                        eventbeingdragged = null; //on efface le dernier event qui était selectionné

                        //on met a jour la var de session
                        majPlanningVolParameters(interactionsouris, selectedeventsarray, updatemoment.format('DD-MM-YYYY'), zoom_y, typedemodificationplanning, typedeplanning,periodecustomdebut,periodecustomfin,selectedIdSaison);
                        firsttimelineplanningrendering = false; //on permet la mise a jour de la timeline

                        //on deselectionne tous les jour de la semaines du planning hebdomadaire
                        $('div[id^="PlHebd_"]').each(function () {

                            $(this).removeClass('badge');
                            $(this).removeClass('badge-success');

                        });

                        //on selectionne le jour de la semaine du planning hebdomadaire
                        $("#PlHebd_" + updatemoment.format('e')).addClass('badge');
                        $("#PlHebd_" + updatemoment.format('e')).addClass('badge-success');

                    }
                }
            },
            weekLessCustomButton: {
                text: '',
                click: function() {

                    var curentmoment = $('#calendar').fullCalendar('getDate');

                    if(typedeplanning == 1) {//planning hebdomadaire ouvert

                        //on detruits kes plannings hebdomadaire
                        destroyAllHebdomadaireCaldendars();

                        //on deselectionne tous les jour de la semaines du planning hebdomadaire
                        $('div[id^="PlHebd_"]').each(function(){
                            //$(this).css("color","");
                            $(this).removeClass('badge');
                            $(this).removeClass('badge-success');
                        });

                        $("#bloccalendrierhebdomadaire").css("display","");
                        $('#leplanninghebdomadaire').css('opacity','0.0');

                        selectedeventsarray = []; //on vide le tableau la modif viens d'étre effectuee
                        eventbeingdragged = null; //on efface le dernier event qui était selectionné

                        //on efface le contenu des infos saisons du planning hebdomadaire
                        $("div[id^='infodebutfinsaison_']").html('&nbsp;');

                        var curentmoment = $('#calendar').fullCalendar('getDate');
                        var updatemoment = curentmoment.subtract(7, 'days');
                        $('#calendar').fullCalendar('gotoDate', updatemoment);
                        curentmoment = $('#calendar').fullCalendar('getDate');

                        //on selectionne le jour de la semaine du planning hebdomadaire
                        $("#PlHebd_"+curentmoment.format('e')).addClass('badge');
                        $("#PlHebd_"+curentmoment.format('e')).addClass('badge-success');

                        //on met a jour la var de session
                        var curentmoment = $('#calendar').fullCalendar('getDate');
                        majPlanningVolParameters(interactionsouris,selectedeventsarray,curentmoment.format('DD-MM-YYYY'),zoom_y,typedemodificationplanning,typedeplanning,periodecustomdebut,periodecustomfin,selectedIdSaison);

                        //on lance l'affichage du résumé de la semaine (preview des planning de lundi à dimanche)
                        createAllHebdomadaireCaldendars(curentmoment);

                    }else {

                        var curentmoment = $('#calendar').fullCalendar('getDate');
                        var updatemoment = curentmoment.subtract(7, 'days');
                        $('#calendar').fullCalendar('gotoDate', updatemoment);
                        curentmoment = $('#calendar').fullCalendar('getDate');

                        selectedeventsarray = []; //on vide le tableau la modif viens d'étre effectuee
                        eventbeingdragged = null; //on efface le dernier event qui était selectionné

                        //on met a jour la var de session
                        majPlanningVolParameters(interactionsouris, selectedeventsarray, updatemoment.format('DD-MM-YYYY'), zoom_y, typedemodificationplanning, typedeplanning,periodecustomdebut,periodecustomfin,selectedIdSaison);
                        firsttimelineplanningrendering = false; //on permet la mise a jour de la timeline

                        //on deselectionne tous les jour de la semaines du planning hebdomadaire
                        $('div[id^="PlHebd_"]').each(function () {

                            $(this).removeClass('badge');
                            $(this).removeClass('badge-success');

                        });

                        //on deselectionne le jour de la semaine du planning hebdomadaire
                        $("#PlHebd_" + updatemoment.format('e')).addClass('badge');
                        $("#PlHebd_" + updatemoment.format('e')).addClass('badge-success');

                    }
                }
            },
            interactionOnOffCustomButton: {
                text: '',
                click: function() {
                    interactionsouris = !interactionsouris;
                    //on met a jour la var de session
                    var curentmoment = $('#calendar').fullCalendar('getDate');
                    majPlanningVolParameters(interactionsouris,selectedeventsarray,curentmoment.format('DD-MM-YYYY'),zoom_y,typedemodificationplanning,typedeplanning,periodecustomdebut,periodecustomfin,selectedIdSaison);

                    if(interactionsouris==false){

                        //on décale le bouton zoom moins pour empécher le décallage lors de l'apparition du bouton erraser lorsque selection souris est sur ON
                        $('.fc-zoomDefaultCustomButton-button').parent().css('margin-left','50px');

                        //on autorise l'ajout de vol dans le menu gauche
                        $(".new_vol_modal").next().remove();
                        $(".new_vol_modal").css('display','');

                        //on empeche les deplacement
                        var tousleseventsarray = $("#calendar").fullCalendar('clientEvents'); //clientEvents retourne un tableau avec utous les éléments
                        tousleseventsarray.forEach(function(unvol) {
                            //maj de l'event
                            unvol.editable = false;
                        });

                        // on efface les icone L des vols selectionnés
                        var unvol;
                        var copieofselectedeventsarray = new Array();
                        copieofselectedeventsarray = selectedeventsarray.slice();
                        selectedeventsarray = []; //on vide le tableau la modif viens d'étre effectuee
                        copieofselectedeventsarray.forEach(function(unidvol) {

                            unvol = $("#calendar").fullCalendar('clientEvents', unidvol); //clientEvents retourne un tableau avec un seul élément
                            $('#calendar').fullCalendar('updateEvent', unvol[0]); //on rafraichi l'event (lors du rendu comme selectedeventsarray est vide les petite icones de selections disparaitrons)

                        });

                        // on efface les icone L des jours du planning hebdomadaire selectionné
                        $('div[id^="PlHebd_"]').each(function(){
                            $(this).removeClass('joursem_sel');
                            $(this).children().first().next().remove();
                            selectedjoursplanninghebdomadairearray = new Array();
                        });

                        //on met a jour la var de session
                        var curentmoment = $('#calendar').fullCalendar('getDate');
                        majPlanningVolParameters(interactionsouris,selectedeventsarray,curentmoment.format('DD-MM-YYYY'),zoom_y,typedemodificationplanning,typedeplanning,periodecustomdebut,periodecustomfin,selectedIdSaison);

                        //on rafraichi le bouton
                        $(".fc-interactionOnOffCustomButton-button").css('color','');
                        $(".fc-interactionOnOffCustomButton-button").removeClass('bg-success');
                        $(".fc-interactionOnOffCustomButton-button").html("<i class='glyphicon glyphicon-remove-sign fa-lg'></i> Sélection à la souris OFF");
                        $(".fc-selectionEraserCustomButton-button").addClass('hide');

                    }else{

                        //le bouton eraser est present a l'ecran on laisse la marge normal au bouton zoom moin
                        $('.fc-zoomDefaultCustomButton-button').parent().css('margin-left','13px');

                        //on interdit l'ajout de vol dans le menu gauche
                        $(".new_vol_modal").css('display','none');
                        $('<span class="tag_a_to_remove_when_selsouris_off" style="line-height:30px;margin-left:37px;cursor: default;color:#bbbbbb;font-size:1.1em;"><span class="fa fa-plus"> </span>  Nouveau vol</span>').insertAfter( ".new_vol_modal" );

                        //on autorise les deplacement
                        var tousleseventsarray = $("#calendar").fullCalendar('clientEvents'); //clientEvents retourne un tableau avec utous les éléments
                        tousleseventsarray.forEach(function(unvol) {

                            if(!unvol.immobilisation){

                                //maj de l'event
                                unvol.editable = true;

                            }

                        });

                        //on rafraichi le bouton
                        $(".fc-interactionOnOffCustomButton-button").css('color','white');
                        $(".fc-interactionOnOffCustomButton-button").addClass('bg-success');
                        $(".fc-interactionOnOffCustomButton-button").html("<i class='glyphicon glyphicon-ok-sign fa-lg'></i> Sélection à la souris ON");
                        $(".fc-selectionEraserCustomButton-button").removeClass('hide');
                    }
                }
            },
            journalierCustomButton: {
                text: 'Journalier',
                click: function() {

                    if (!$('.fc-journalierCustomButton-button').hasClass('btn-success')) { //empéche le clic sur le bouton lorsqu'il est selectionné

                        //on deselectionne tous les jour de la semaines du planning hebdomadaire
                        $('div[id^="PlHebd_"]').each(function () {
                            if ($(this).hasClass('joursem_sel')) {
                                $(this).removeClass('joursem_sel');
                                $(this).children().first().next().remove();
                            }
                            $(this).removeClass('badge');
                            $(this).removeClass('badge-success');
                        });

                        $("#bloccalendrierhebdomadaire").css("display", "none");

                        selectedeventsarray = [];

                        //on efface le contenu des infos saisons du planning hebdomadaire
                        $("div[id^='infodebutfinsaison_']").html('&nbsp;');

                        //on met a jour la var de session
                        var curentmoment = $('#calendar').fullCalendar('getDate');
                        typedeplanning = 0;
                        majPlanningVolParameters(interactionsouris, selectedeventsarray, curentmoment.format('DD-MM-YYYY'), zoom_y, typedemodificationplanning, typedeplanning, periodecustomdebut, periodecustomfin, selectedIdSaison);

                        $(".fc-journalierCustomButton-button").addClass('bg-success');
                        $(".fc-journalierCustomButton-button").addClass('btn-success');
                        $(".fc-journalierCustomButton-button").addClass('bg-success');
                        $(".fc-journalierCustomButton-button").css('cursor', 'default');
                        $(".fc-journalierCustomButton-button").css('color', 'white');

                        $(".fc-hebdomadaireCustomButton-button").removeClass('btn-success');
                        $(".fc-hebdomadaireCustomButton-button").removeClass('bg-success');
                        $(".fc-hebdomadaireCustomButton-button").addClass('btn-default');
                        $(".fc-hebdomadaireCustomButton-button").css('cursor', 'pointer');
                        $(".fc-hebdomadaireCustomButton-button").css('color', '');

                        //empéche le clic sur le bouton Journalier selectionné
                        //$(".fc-journalierCustomButton-button").attr('disabled', 'disabled');
                        //on autorise le click sur le bouton hebdomadaire
                        //$(".fc-hebdomadaireCustomButton-button").removeAttr('disabled');

                        //on lance la destruction du résumé de la semaine (on efface les preview des planning de lundi à dimanche)
                        destroyAllHebdomadaireCaldendars();

                    }

                }
            },
            hebdomadaireCustomButton: {
                text: 'Hebdomadaire',
                click: function() {

                    if (!$('.fc-hebdomadaireCustomButton-button').hasClass('btn-success')) { //empéche le clic sur le bouton lorsqu'il est selectionné

                        //on ouvre le spinner d'attente
                        $("#mySpinnerModal").modal("show");

                        //on deselectionne tous les jour de la semaines du planning hebdomadaire
                        $('div[id^="PlHebd_"]').each(function () {
                            $(this).removeClass('badge');
                            $(this).removeClass('badge-success');
                        });
                        //on selectionne le jour de la semaine du planning hebdomadaire
                        var momtmp = $('#calendar').fullCalendar('getDate');

                        $("#PlHebd_" + momtmp.format('e')).addClass('badge');
                        $("#PlHebd_" + momtmp.format('e')).addClass('badge-success');

                        $("#bloccalendrierhebdomadaire").css("display", "");
                        $("#bloccalendrierhebdomadaire").css("height", "60px");
                        $('#leplanninghebdomadaire').css('opacity', '0.0');

                        selectedeventsarray = [];

                        //on met a jour la var de session
                        var curentmoment = $('#calendar').fullCalendar('getDate');
                        typedeplanning = 1;
                        majPlanningVolParameters(interactionsouris, selectedeventsarray, curentmoment.format('DD-MM-YYYY'), zoom_y, typedemodificationplanning, typedeplanning, periodecustomdebut, periodecustomfin, selectedIdSaison);

                        $(".fc-hebdomadaireCustomButton-button").removeClass('btn-default');
                        $(".fc-hebdomadaireCustomButton-button").addClass('btn-success');
                        $(".fc-hebdomadaireCustomButton-button").addClass('bg-success');
                        $(".fc-hebdomadaireCustomButton-button").css('cursor', 'default');
                        $(".fc-hebdomadaireCustomButton-button").css('color', 'white');

                        $(".fc-journalierCustomButton-button").removeClass('btn-success');
                        $(".fc-journalierCustomButton-button").removeClass('bg-success');
                        $(".fc-journalierCustomButton-button").addClass('btn-default');
                        $(".fc-journalierCustomButton-button").css('cursor', 'pointer');
                        $(".fc-journalierCustomButton-button").css('color', '');

                        //empéche le clic sur le bouton Hebdomadaire selectionné
                        //$(".fc-hebdomadaireCustomButton-button").attr('disabled', 'disabled');
                        //on autorise le click sur le bouton journalier
                        //$(".fc-journalierCustomButton-button").removeAttr('disabled');


                        //on lance l'affichage du résumé de la semaine (preview des planning de lundi à dimanche)
                        createAllHebdomadaireCaldendars(curentmoment);

                    }
                }
            },
            timeline24hCustomButton: {
                text: '00h-23h00',
                click: function() {

                    if (!$(".fc-timeline24hCustomButton-button").hasClass('btn-success')) { //empéche le clic sur le bouton lorsqu'il est selectionné

                        //on met a jour la var de session
                        var curentmoment = $('#calendar').fullCalendar('getDate');

                        timelinesechellevisualisation = 0;
                        firsttimelineplanningrendering = true;

                        //empéche le clic sur le bouton 00h-23h selectionné
                        $(".fc-timeline24hCustomButton-button").removeClass('btn-default');
                        $(".fc-timeline24hCustomButton-button").addClass('btn-success');
                        $(".fc-timeline24hCustomButton-button").addClass('bg-success');
                        $(".fc-timeline24hCustomButton-button").css('cursor', 'default');
                        $(".fc-timeline24hCustomButton-button").css('color', 'white');
                        //$(".fc-timeline24hCustomButton-button").attr('disabled','disabled');
                        //on autorise le click sur le bouton 04h-23h
                        $(".fc-timeline20hCustomButton-button").removeClass('btn-success');
                        $(".fc-timeline20hCustomButton-button").removeClass('bg-success');
                        $(".fc-timeline20hCustomButton-button").addClass('btn-default');
                        $(".fc-timeline20hCustomButton-button").css('cursor', 'pointer');
                        $(".fc-timeline20hCustomButton-button").css('color', '');
                        //$(".fc-timeline20hCustomButton-button").removeAttr('disabled');

                        majPlanningVolParameters(interactionsouris, selectedeventsarray, curentmoment.format('DD-MM-YYYY'), zoom_y, typedemodificationplanning, typedeplanning, periodecustomdebut, periodecustomfin, selectedIdSaison);

                        $('#calendar').fullCalendar('option', 'minTime', '00:00'); //on modifie la timeline

                    }

                }
            },
            timeline20hCustomButton: {
                text: '04h-23h',
                click: function() {

                    if (!$(".fc-timeline20hCustomButton-button").hasClass('btn-success')) { //empéche le clic sur le bouton lorsqu'il est selectionné

                        //on met a jour la var de session
                        var curentmoment = $('#calendar').fullCalendar('getDate');

                        timelinesechellevisualisation = 1;
                        firsttimelineplanningrendering = true;

                        //empéche le clic sur le bouton 04h-23h selectionné
                        $(".fc-timeline20hCustomButton-button").removeClass('btn-default');
                        $(".fc-timeline20hCustomButton-button").addClass('btn-success');
                        $(".fc-timeline20hCustomButton-button").addClass('bg-success');
                        $(".fc-timeline20hCustomButton-button").css('cursor', 'default');
                        $(".fc-timeline20hCustomButton-button").css('color', 'white');
                        //$(".fc-timeline20hCustomButton-button").attr('disabled','disabled');
                        //on autorise le click sur le bouton 00h-23h
                        $(".fc-timeline24hCustomButton-button").removeClass('btn-success');
                        $(".fc-timeline24hCustomButton-button").removeClass('bg-success');
                        $(".fc-timeline24hCustomButton-button").addClass('btn-default');
                        $(".fc-timeline24hCustomButton-button").css('cursor', 'pointer');
                        $(".fc-timeline24hCustomButton-button").css('color', '');
                        //$(".fc-timeline24hCustomButton-button").removeAttr('disabled');

                        majPlanningVolParameters(interactionsouris, selectedeventsarray, curentmoment.format('DD-MM-YYYY'), zoom_y, typedemodificationplanning, typedeplanning, periodecustomdebut, periodecustomfin, selectedIdSaison);

                        $('#calendar').fullCalendar('option', 'minTime', '04:00');

                    }

                }
            },
            enlargeShrinkCustomButton: {
                text: '',
                click: function() {
                    if($("#sidebar_left").width() > 60) {
                        $(".sidebar-toggle-mini").trigger("click");
                        $(".navbar-fixed-top").css('display','none');
                        $("#content_wrapper").css('padding','0px');
                        $("#sidebar_left").css('padding','0px');
                        $(".fc-enlargeShrinkCustomButton-button").addClass('bg-success');
                        $(".fc-enlargeShrinkCustomButton-button").css('color', 'white');
                    }else{
                        $(".navbar-fixed-top").css('display','');
                        $("#content_wrapper").css('padding','');
                        $("#sidebar_left").css('padding','');
                        $("div#main").children().children("div").children("span").trigger( "click" );
                        $(".fc-enlargeShrinkCustomButton-button").removeClass('bg-success');
                        $(".fc-enlargeShrinkCustomButton-button").css('color', '');
                    }
                }
            },
            printCustomButton: {
                text: '',
                click: function() {

                    //fix print periode date debutsemmaine et finsemaine courante
                    var printupdatemoment = $('#calendar').fullCalendar('getDate');
                    $('#printdatedebut').val( printupdatemoment.startOf('isoWeek').format('DD-MM-YYYY') );
                    $('#printdatefin').val( printupdatemoment.endOf('isoWeek').format('DD-MM-YYYY') );

                    //on fait un reset de la gestion des evenement dans ce modal
                    $('#myPDFPrinterModal').unbind();

                    //on affiche le modal
                    $('#myPDFPrinterModal').modal('show');

                    //lorsque le modal s'ouvre
                    $('#myPDFPrinterModal').on('shown.bs.modal', function () {

                        var datefin;
                        var datedebut;
                        var aJoursDeLaSemaine;
                        var optionImpressionAvions;
                        var aIdAvionsAImprimer;
                        var impressionEnNoirEtBlanc;
                        var margegauche;
                        var margehaute;
                        var margedroite;
                        var autoResizeTranchesHoraires;
                        var fontautoresize;
                        var reperesdurees;
                        var volscourtsoverflow;
                        var dureemaxivolscourts;
                        var enteteaircorsica;
                        var entetejoursemaine;
                        var entetenumerosemaine;
                        var entetesaison;
                        var entetetypehoraire;
                        var entetedateimpression;
                        var entetenumerodepage;
                        var url;
                        var varsget;
                        var printSend = false;
                        var affichagetranchehorairejoursuivant;


                        //lancement de l'impression
                        $('#myPDFPrinterModal').on('hide.bs.modal', function () {
                            //si on a demandé une impression PDF
                            if(printSend == true){

                                //message d'attente lors du chargement
                                $("#mySpinnerModal").modal("show");

                                //on affiche le pdf sur une nouvelle oage blank
                                var pdfNewWindow = window.open(url+'?'+varsget,'_blank');
                                pdfNewWindow.onload=function(){
                                    $("#mySpinnerModal").modal("hide");
                                };


                            }
                        });

                        // annulation
                        $("#impressionANNULER").bind("click", function () {
                            //on ferme le modal
                            $('#myPDFPrinterModal').modal('hide');
                        });

                        // validation
                        $( "#impressionLANCER" ).bind("click", function () {

                            url = Routing.generate('planningvol_printPDFPlanningVols');

                            //les dates de début et de fin de l'impression
                            if($('#radio_jr').prop("checked") == true){
                                var curentmoment = $('#calendar').fullCalendar('getDate');
                                datedebut = curentmoment.format('YYYY-MM-DD'); //2016-05-20
                                datefin = datedebut;
                            }else{
                                var momentDeb = moment ($('#printdatedebut').val(),'DD-MM-YYYY');
                                datedebut = momentDeb.format('YYYY-MM-DD');
                                var momentFin = moment ($('#printdatefin').val(),'DD-MM-YYYY');
                                datefin = momentFin.format('YYYY-MM-DD');
                            }

                            //les jours de la semaine
                            aJoursDeLaSemaine = '';
                            var i = 0;
                            $('input[id^="chkbx_jsem"]').each( function () {
                                if($(this).prop("checked") == true){
                                    aJoursDeLaSemaine+=i+'_'
                                }else{
                                    aJoursDeLaSemaine+='-_'
                                }
                                i++;
                            });
                            aJoursDeLaSemaine = aJoursDeLaSemaine.slice(0,-1); //on enléve la derniére virgule

                            //les options affichage avions
                            optionImpressionAvions;
                            $('input[name="options9"]').each( function () {
                                if($(this).prop("checked") == true){
                                    optionImpressionAvions = $(this).attr("data-valoption");
                                }
                            });

                            //les avions selectionné
                            aIdAvionsAImprimer = '';
                            $('input[id^="chkbx_pavion"]').each( function () {
                                if($(this).prop("checked") == true){
                                    aIdAvionsAImprimer+=$(this).val()+'_'
                                }
                            });
                            aIdAvionsAImprimer = aIdAvionsAImprimer.slice(0,-1); //on enléve la derniére virgule

                            //impression en noir et blanc
                            if($("#radio_nb").prop("checked") == true){
                                impressionEnNoirEtBlanc = true;
                            }else{
                                impressionEnNoirEtBlanc = false;
                            }

                            //marge droite
                            margegauche = $("#inp_mgauche").val();

                            //marge haute
                            margehaute = $("#inp_mtop").val();

                            //margegauche
                            margedroite = $("#inp_mdroite").val();

                            //les tranches horaires
                            if($("#chkbx_trchhoraire").prop("checked") == true){
                                autoResizeTranchesHoraires = true;
                            }else{
                                autoResizeTranchesHoraires = false;
                            }

                            //les tailles de la police des elements du vols
                            if($("#chkbx_fontautoresize").prop("checked") == true){
                                fontautoresize = true;
                            }else{
                                fontautoresize = false;
                            }

                            //les repéres de durée des vols
                            if($("#chkbx_reperesdurees").prop("checked") == true){
                                reperesdurees = true;
                            }else{
                                reperesdurees = false;
                            }

                            //overflow des vols courts
                            if($("#chkbx_vlscourtoverflow").prop("checked") == true){
                                volscourtsoverflow = true;
                            }else{
                                volscourtsoverflow = false;
                            }

                            //durée de vol considérant un vol comme court
                            dureemaxivolscourts = $("#inp_dureemaxivolscourts").val();

                            //en-tete AirCorsica
                            if($("#chkbx_enteteaircorsica").prop("checked") == true){
                                enteteaircorsica = true;
                            }else{
                                enteteaircorsica = false;
                            }

                            //en-tete jour de la semaine
                            if($("#chkbx_entetejoursemaine").prop("checked") == true){
                                entetejoursemaine = true;
                            }else{
                                entetejoursemaine = false;
                            }

                            //en-tete numero de la semaine
                            if($("#chkbx_entetenumerosemaine").prop("checked") == true){
                                entetenumerosemaine = true;
                            }else{
                                entetenumerosemaine = false;
                            }

                            //en-tete saison
                            if($("#chkbx_entetesaison").prop("checked") == true){
                                entetesaison = true;
                            }else{
                                entetesaison = false;
                            }

                            //en-tete type horaire
                            if($("#chkbx_entetetypehoraire").prop("checked") == true){
                                entetetypehoraire = true;
                            }else{
                                entetetypehoraire = false;
                            }

                            //en-tete type horaire
                            if($("#chkbx_entetedateimpression").prop("checked") == true){
                                entetedateimpression = true;
                            }else{
                                entetedateimpression = false;
                            }

                            //en-tete type horaire
                            if($("#chkbx_entetenumerodepage").prop("checked") == true){
                                entetenumerodepage = true;
                            }else{
                                entetenumerodepage = false;
                            }

                            //tranche horaire du jours suiavnt jusqu'a l'atterissage pour les vol a cheval sur 2 jours
                            if($("#chkbx_trchhorairesupjourcournt").prop("checked") == true){
                                affichagetranchehorairejoursuivant = true;
                            }else{
                                affichagetranchehorairejoursuivant = false;
                            }

                            //on crée les variables GET à transmettre
                            varsget = 'dateDebut='+datedebut+
                                '&dateFin='+datefin+
                                '&aJoursDeLaSemaine='+aJoursDeLaSemaine+
                                '&optionImpressionAvions='+optionImpressionAvions+
                                '&aIdAvionsAImprimer='+aIdAvionsAImprimer+
                                '&impressionEnNoirEtBlanc='+impressionEnNoirEtBlanc+
                                '&margegauche='+margegauche+
                                '&margehaute='+margehaute+
                                '&margedroite='+margedroite+
                                '&autoResizeFonts='+fontautoresize+
                                '&reperesdurees='+reperesdurees+
                                '&overflowVolsCourts='+volscourtsoverflow+
                                '&dureemaxivolscourts='+dureemaxivolscourts+
                                '&enteteaircorsica='+enteteaircorsica+
                                '&entetejoursemaine='+entetejoursemaine+
                                '&entetenumerosemaine='+entetenumerosemaine+
                                '&entetesaison='+entetesaison+
                                '&entetetypehoraire='+entetetypehoraire+
                                '&entetedateimpression='+entetedateimpression+
                                '&entetenumerodepage='+entetenumerodepage+
                                '&autoResizeTranchesHoraires='+autoResizeTranchesHoraires+
                                '&affichagetranchehorairejoursuivant='+affichagetranchehorairejoursuivant;

                            // l'impression à été demandée
                            printSend = true;

                            //on ferme le modal
                            $('#myPDFPrinterModal').modal('hide');

                        });


                    });

                }
            },
            zoomDefaultCustomButton: {
                text: '',
                click: function() {

                    // resize Y
                    zoom_y=zoom_y_default;

                    //on met a jour la var de session
                    var curentmoment = $('#calendar').fullCalendar('getDate');
                    majPlanningVolParameters(interactionsouris,selectedeventsarray,curentmoment.format('DD-MM-YYYY'),zoom_y,typedemodificationplanning,typedeplanning,periodecustomdebut,periodecustomfin,selectedIdSaison);

                    $("#calendar .fc-rows table tbody tr .fc-widget-content div").css("height",Math.floor(defaultEventHeight+((defaultEventHeight*zoom_y)/10))+"px");
                    $("#calendar .fc-content table tbody tr .fc-widget-content div").css("height",Math.floor(defaultEventHeight+((defaultEventHeight*zoom_y)/10))+"px");
                    $("#calendar .fc-body .fc-resource-area .fc-cell-content").css("height",Math.floor(defaultEventHeight+((defaultEventHeight*zoom_y)/10))+"px");
                    $("#calendar div .fc-event-container").css("height",Math.floor(defaultEventHeight+((defaultEventHeight*zoom_y)/10))+"px");
                    $("#calendar div .fc-event-container a").css("height",(Math.floor(defaultEventHeight+((defaultEventHeight*zoom_y)/10))-12)+"px");

                    //on regénére les events du planning
                    $('#calendar').fullCalendar( 'rerenderEvents' );

                }
            },
            zoomPlusCustomButton: {
                text: '',
                click: function() {

                    if(zoom_y<10){

                        //resize y
                        zoom_y = zoom_y +1;

                        //on met a jour la var de session
                        var curentmoment = $('#calendar').fullCalendar('getDate');
                        majPlanningVolParameters(interactionsouris,selectedeventsarray,curentmoment.format('DD-MM-YYYY'),zoom_y,typedemodificationplanning,typedeplanning,periodecustomdebut,periodecustomfin,selectedIdSaison);

                        $("#calendar .fc-rows table tbody tr .fc-widget-content div").css("height",Math.floor(defaultEventHeight+((defaultEventHeight*zoom_y)/10))+"px");
                        $("#calendar .fc-content table tbody tr .fc-widget-content div").css("height",Math.floor(defaultEventHeight+((defaultEventHeight*zoom_y)/10))+"px");
                        $("#calendar .fc-body .fc-resource-area .fc-cell-content").css("height",Math.floor(defaultEventHeight+((defaultEventHeight*zoom_y)/10))+"px");
                        $("#calendar div .fc-event-container").css("height",Math.floor(defaultEventHeight+((defaultEventHeight*zoom_y)/10))+"px");
                        $("#calendar div .fc-event-container a").css("height",(Math.floor(defaultEventHeight+((defaultEventHeight*zoom_y)/10))-12)+"px");

                        //on regénére les events du planning
                        $('#calendar').fullCalendar( 'rerenderEvents' );

                    }else{
                        bootbox.alert("<i class='glyphicon glyphicon-alert' style='color: red;'></i> Vous avez atteind le zoom maximum.");
                    }

                }
            },
            zoomMoinsCustomButton: {
                text: '',
                click: function() {

                    if(zoom_y>zoom_y_mini){

                        //resize y
                        zoom_y = zoom_y -1;

                        //on met a jour la var de session
                        var curentmoment = $('#calendar').fullCalendar('getDate');
                        majPlanningVolParameters(interactionsouris,selectedeventsarray,curentmoment.format('DD-MM-YYYY'),zoom_y,typedemodificationplanning,typedeplanning,periodecustomdebut,periodecustomfin,selectedIdSaison);

                        $("#calendar .fc-rows table tbody tr .fc-widget-content div").css("height",Math.floor(defaultEventHeight+((defaultEventHeight*zoom_y)/10))+"px");
                        $("#calendar .fc-content table tbody tr .fc-widget-content div").css("height",Math.floor(defaultEventHeight+((defaultEventHeight*zoom_y)/10))+"px");
                        $("#calendar .fc-body .fc-resource-area .fc-cell-content").css("height",Math.floor(defaultEventHeight+((defaultEventHeight*zoom_y)/10))+"px");
                        $("#calendar div .fc-event-container").css("height",Math.floor(defaultEventHeight+((defaultEventHeight*zoom_y)/10))+"px");
                        $("#calendar div .fc-event-container a").css("height",(Math.floor(defaultEventHeight+((defaultEventHeight*zoom_y)/10))-12)+"px");

                        //on regénére les events du planning
                        $('#calendar').fullCalendar( 'rerenderEvents' );

                    }else{
                        bootbox.alert("<i class='glyphicon glyphicon-alert' style='color: red;'></i> Vous avez atteind le zoom minimum.");
                    }

                }
            }
        },
        defaultView: 'timelineDay',
        viewRender: function( view, element ){

            //on desactive le bouton + Nouveau Vol du menu latéral gauche au cas ou l'on reviénne sur le plannning Vols et que l'option selection à la souris était sur ON
            if(interactionsouris == true){
                //le bouton eraser est present a l'ecran on laisse la marge normal au bouton zoom moin
                $('.fc-zoomDefaultCustomButton-button').parent().css('margin-left','13px');

                if($('.tag_a_to_remove_when_selsouris_off').length == 0) {
                    //on interdit l'ajout de vol dans le menu gauche
                    $(".new_vol_modal").css('display', 'none');
                    $('<span class="tag_a_to_remove_when_selsouris_off" style="line-height:30px;margin-left:37px;cursor: default;color:#bbbbbb;font-size:1.1em;"><span class="fa fa-plus"> </span>  Nouveau vol</span>').insertAfter(".new_vol_modal");
                }
            }else{
                //on décale le bouton zoom moins pour empécher le décallage lors de l'apparition du bouton erraser lorsque selection souris est sur ON
                $('.fc-zoomDefaultCustomButton-button').parent().css('margin-left','50px');
            }



            //on synchronise la date du datepicker
            var curentmoment = $('#calendar').fullCalendar('getDate');
            $(".datepicker1").datepicker('setDate', curentmoment.format('MM-DD-YYYY'));

            //on synchronise la saison selectionnée en fonction de la date
            var tempdatecourante = curentmoment.format('YYYY-MM-DD');
            // Fix la date de la saison n'est pas passé lors d'une modification a la souris
            //$("#saisonselect1 option").each(function(i){
            //
            //    var tempdatefin = moment($(this).attr('data-datefinperiodesaison'));
            //    var tempdatedebut = moment($(this).attr('data-datedebutperiodesaison'));
            //
            //    if( (moment(tempdatecourante).isSameOrBefore(moment(tempdatefin))) && (moment(tempdatecourante).isSameOrAfter(moment(tempdatedebut))) ){
            //
            //        $(this).prop("selected", "selected");
            //        $("#select2-saisonselect1-container").text($(this).text());
            //
            //    }
            //
            //});


            //on enlève la ligne de resize des resources
            $('.fc-col-resizer').remove();

            //icone devant l'eraser de selection
            $(".fc-selectionEraserCustomButton-button").html("<i class='glyphicon glyphicon-erase fa-lg'></i> ");

            //icones devant les boutons de zoom
            $(".fc-zoomDefaultCustomButton-button").html("<i class='glyphicon glyphicon-search fa-lg'></i> ");
            $(".fc-zoomPlusCustomButton-button").html("<i class='glyphicon glyphicon-zoom-in fa-lg'></i> ");
            $(".fc-zoomMoinsCustomButton-button").html("<i class='glyphicon glyphicon-zoom-out fa-lg'></i> ");
            //icone pour fermer/ouvrir la barre de menu
            $(".fc-enlargeShrinkCustomButton-button").html("<i class='glyphicon glyphicon-transfer fa-lg'></i>");
            //icone pour le bouton impression
            $(".fc-printCustomButton-button").html("<i class='glyphicon glyphicon-print fa-lg'></i>");
            //icone pour une semaine de moins
            $(".fc-weekLessCustomButton-button").html("<i class='glyphicon glyphicon-backward fa-lg'></i> ");
            //icone pour le bouton prev
            $(".fc-prevCustomButton-button").html("<i class='glyphicon glyphicon-arrow-left fa-lg'></i> ");
            //icone pour le bouton refresh
            $(".fc-refreshCustomButton-button").html("<i class='glyphicon glyphicon-repeat fa-lg'></i> ");
            //icone pour le bouton next
            $(".fc-nextCustomButton-button").html("<i class='glyphicon glyphicon-arrow-right fa-lg'></i> ");
            //icone pour une semaine de plus
            $(".fc-weekMoreCustomButton-button").html("<i class='glyphicon glyphicon-forward fa-lg'></i> ");
            //icone pour une année de plus
            $(".fc-nextMonthCustomButton-button").html("<i class='glyphicon glyphicon-fast-forward fa-lg'></i> ");
            //icone pour une année de moins
            $(".fc-previousMonthCustomButton-button").html("<i class='glyphicon glyphicon-fast-backward fa-lg'></i> ");

            // Aide su les boutons d'interface avec des petits tooltips bootstrap
            // ------------------------------------------------------------------
            if(affichageDesTooltipsAidePourBoutonsInterface == true) {

                //toolrip sur le bouton "Ponctuelle" déselectionné
                $("#sw_ponctuelle").attr("data-toggle", "tooltip");
                $("#sw_ponctuelle").attr("data-placement", "bottom");
                $("#sw_ponctuelle").attr("title", "Les modification d'avion s'applique sur le jour courant ou sur des jours selectionnés de la semaine courante.");

                //toolrip sur le bouton "Saison" déselectionné
                $("#sw_saisoncourante").attr("data-toggle", "tooltip");
                $("#sw_saisoncourante").attr("data-placement", "bottom");
                $("#sw_saisoncourante").attr("title", "Les modification d'avion s'applique sur les périodes de vols incluses dans la saison sélectionnée.");

                //toolrip sur le bouton "Période" déselectionné
                $("#sw_periode").attr("data-toggle", "tooltip");
                $("#sw_periode").attr("data-placement", "bottom");
                $("#sw_periode").attr("title", "Les modification d'avion s'applique sur les périodes de vols incluses dans la période custom définie par l'utilisateur.");

                //tooltip sur le bouton "Journalier" deselectionné
                $(".fc-journalierCustomButton-button").attr("data-toggle", "tooltip");
                $(".fc-journalierCustomButton-button").attr("data-placement", "bottom");
                $(".fc-journalierCustomButton-button").attr("title", "Affiche le planning sur un jour.");

                //tooltip sur le bouton "hebdomadaire déselectionné
                $(".fc-hebdomadaireCustomButton-button").attr("data-toggle", "tooltip");
                $(".fc-hebdomadaireCustomButton-button").attr("data-placement", "bottom");
                $(".fc-hebdomadaireCustomButton-button").attr("title", "Affiche le planning sur la semaine du jour courant.");

                //tooltip sur le bouton "selection à la souris" déselectionné
                $(".fc-interactionOnOffCustomButton-button").attr("data-toggle", "tooltip");
                $(".fc-interactionOnOffCustomButton-button").attr("data-placement", "bottom");
                $(".fc-interactionOnOffCustomButton-button").attr("title", "Active la selection de vol(s) et de jour(s) si le planning hebdomadaire est visible.\nLa création et la modification de vol est indisponible lorsque cette option est activée.");

                //tooltip sur le bouton "eraser"
                $(".fc-selectionEraserCustomButton-button").attr("data-toggle", "tooltip");
                $(".fc-selectionEraserCustomButton-button").attr("data-placement", "bottom");
                $(".fc-selectionEraserCustomButton-button").attr("title", "Efface toutes les selections existantes.");

                //tooltip sur le bouton "zoom -"
                $(".fc-zoomMoinsCustomButton-button").attr("data-toggle", "tooltip");
                $(".fc-zoomMoinsCustomButton-button").attr("data-placement", "bottom");
                $(".fc-zoomMoinsCustomButton-button").attr("title", "Diminue l'echelle vertical d'une unité du planning journalier.");

                //tooltip sur le bouton "zoom par default"
                $(".fc-zoomDefaultCustomButton-button").attr("data-toggle", "tooltip");
                $(".fc-zoomDefaultCustomButton-button").attr("data-placement", "bottom");
                $(".fc-zoomDefaultCustomButton-button").attr("title", "Fixe l'echelle vertical du planning journalier à sa valeur par défaut.");

                //tooltip sur le bouton "zoom +"
                $(".fc-zoomPlusCustomButton-button").attr("data-toggle", "tooltip");
                $(".fc-zoomPlusCustomButton-button").attr("data-placement", "bottom");
                $(".fc-zoomPlusCustomButton-button").attr("title", "Augmente l'echelle vertical d'une unité du planning journalier.");

                //tooltip sur le bouton "shrink"
                $(".fc-enlargeShrinkCustomButton-button").attr("data-toggle", "tooltip");
                $(".fc-enlargeShrinkCustomButton-button").attr("data-placement", "bottom");
                $(".fc-enlargeShrinkCustomButton-button").attr("title", "Optimise l'affichage de la page en faveur de la taille du planning.");

                //tooltip sur le bouton "refresh"
                $(".fc-refreshCustomButton-button").attr("data-toggle", "tooltip");
                $(".fc-refreshCustomButton-button").attr("data-placement", "bottom");
                $(".fc-refreshCustomButton-button").attr("title", "Remet toutes les options et choix à défaut\nEfface les selections\nRecharge le plannning courant.");

                //tooltip sur le bouton "print"
                $(".fc-printCustomButton-button").attr("data-toggle", "tooltip");
                $(".fc-printCustomButton-button").attr("data-placement", "bottom");
                $(".fc-printCustomButton-button").attr("title", "Affiche l'interface d'impression du planning.");

                //tooltip sur le bouton "today"
                $(".fc-todayCustomButton-button").attr("data-toggle", "tooltip");
                $(".fc-todayCustomButton-button").attr("data-placement", "bottom");
                $(".fc-todayCustomButton-button").attr("title", "Affiche le planning à la date d'aujourd'hui.");

                //tooltip sur le bouton "previuous month"
                $(".fc-previousMonthCustomButton-button").attr("data-toggle", "tooltip");
                $(".fc-previousMonthCustomButton-button").attr("data-placement", "bottom");
                $(".fc-previousMonthCustomButton-button").attr("title", "Affiche le planning du mois précédent la date courante.");

                //tooltip sur le bouton "previuous week"
                $(".fc-weekLessCustomButton-button").attr("data-toggle", "tooltip");
                $(".fc-weekLessCustomButton-button").attr("data-placement", "bottom");
                $(".fc-weekLessCustomButton-button").attr("title", "Affiche le planning de la semaine précédent la date courante.");

                //tooltip sur le bouton "previuous day"
                $(".fc-prevCustomButton-button").attr("data-toggle", "tooltip");
                $(".fc-prevCustomButton-button").attr("data-placement", "bottom");
                $(".fc-prevCustomButton-button").attr("title", "Affiche le planning du jour précédent la date courante.");

                //tooltip sur le bouton "next day"
                $(".fc-nextCustomButton-button").attr("data-toggle", "tooltip");
                $(".fc-nextCustomButton-button").attr("data-placement", "bottom");
                $(".fc-nextCustomButton-button").attr("title", "Affiche le planning du jour suivant la date courante.");

                //tooltip sur le bouton "next week"
                $(".fc-weekMoreCustomButton-button").attr("data-toggle", "tooltip");
                $(".fc-weekMoreCustomButton-button").attr("data-placement", "bottom");
                $(".fc-weekMoreCustomButton-button").attr("title", "Affiche le planning de la semaine suivant la date courante.");

                //tooltip sur le bouton "next month"
                $(".fc-nextMonthCustomButton-button").attr("data-toggle", "tooltip");
                $(".fc-nextMonthCustomButton-button").attr("data-placement", "bottom");
                $(".fc-nextMonthCustomButton-button").attr("title", "Affiche le planning du mois suivant la date courante.");

                //tooltip cas particulier du bouton "planning sur 24h"
                $('.fc-timeline24hCustomButton-button').attr("data-toggle", "tooltip");
                $('.fc-timeline24hCustomButton-button').attr("data-placement", "bottom");
                $('.fc-timeline24hCustomButton-button').attr("title", "Augmente l'echelle de temps du planning à 24h(permet l'affichage des vols partants le jour précédent).");

                //tooltip cas particulier du bouton "planning sur 24h"
                $('.fc-timeline20hCustomButton-button').attr("data-toggle", "tooltip");
                $('.fc-timeline20hCustomButton-button').attr("data-placement", "bottom");
                $('.fc-timeline20hCustomButton-button').attr("title", "Diminue l'echelle de temps du planning à 20h(echelle de temps par défaut).");

            }


            //couleur du bouton shrink/unshrink view
            if($("#sidebar_left").width() > 60) {
                $(".fc-enlargeShrinkCustomButton-button").removeClass('bg-success');
                $(".fc-enlargeShrinkCustomButton-button").css('color', '');
            }else{
                $(".fc-enlargeShrinkCustomButton-button").addClass('bg-success');
                $(".fc-enlargeShrinkCustomButton-button").css('color', 'white');
            }

            // couleur boutons journalier / hebdomadaire
            if(typedeplanning==0) {
                //empéche le clic sur le bouton Journalier selectionné
                $(".fc-journalierCustomButton-button").removeClass('btn-default');
                $(".fc-journalierCustomButton-button").addClass('btn-success');
                $(".fc-journalierCustomButton-button").addClass('bg-success');
                $(".fc-journalierCustomButton-button").css('cursor', 'default');
                $(".fc-journalierCustomButton-button").css('color', 'white');
                //$(".fc-journalierCustomButton-button").attr('disabled','disabled');
                //on autorise le click sur le bouton hebdomadaire
                $(".fc-hebdomadaireCustomButton-button").removeClass('btn-success');
                $(".fc-hebdomadaireCustomButton-button").removeClass('bg-success');
                $(".fc-hebdomadaireCustomButton-button").addClass('btn-default');
                $(".fc-hebdomadaireCustomButton-button").css('cursor', 'pointer');
                $(".fc-hebdomadaireCustomButton-button").css('color', '');
                //$(".fc-hebdomadaireCustomButton-button").removeAttr('disabled');
            }else{
                //empéche le clic sur le bouton hebdomadaire selectionné
                $(".fc-hebdomadaireCustomButton-button").removeClass('btn-default');
                $(".fc-hebdomadaireCustomButton-button").addClass('btn-success');
                $(".fc-hebdomadaireCustomButton-button").addClass('bg-success');
                $(".fc-hebdomadaireCustomButton-button").css('cursor', 'default');
                $(".fc-hebdomadaireCustomButton-button").css('color', 'white');
                //$(".fc-hebdomadaireCustomButton-button").attr('disabled','disabled');
                //on autorise le click sur le bouton journalier
                $(".fc-journalierCustomButton-button").removeClass('btn-success');
                $(".fc-journalierCustomButton-button").removeClass('bg-success');
                $(".fc-journalierCustomButton-button").addClass('btn-default');
                $(".fc-journalierCustomButton-button").css('cursor', 'pointer');
                $(".fc-journalierCustomButton-button").css('color', '');
                //$(".fc-journalierCustomButton-button").removeAttr('disabled');
            }

            if(affichageplanningavant4h==true) {
                if (timelinesechellevisualisation == 0) {
                    //on décolore le bouton 04h-23h
                    $(".fc-timeline20hCustomButton-button").removeClass('btn-success');
                    $(".fc-timeline20hCustomButton-button").removeClass('bg-success');
                    $(".fc-timeline20hCustomButton-button").addClass('btn-default');
                    $(".fc-timeline20hCustomButton-button").css('cursor', 'pointer');
                    $(".fc-timeline20hCustomButton-button").css('color', '');
                    //on autorise le click sur le bouton 04h-23h
                    //$(".fc-timeline20hCustomButton-button").removeAttr('disabled');
                    //on colore ON le bouton 00h-23h
                    $(".fc-timeline24hCustomButton-button").removeClass('btn-default');
                    $(".fc-timeline24hCustomButton-button").addClass('btn-success');
                    $(".fc-timeline24hCustomButton-button").addClass('bg-success');
                    $(".fc-timeline24hCustomButton-button").css('cursor', 'default');
                    $(".fc-timeline24hCustomButton-button").css('color', 'white');
                    //empéche le clic sur le bouton 04h-23h selectionné
                    //$(".fc-timeline24hCustomButton-button").attr('disabled','disabled');

                } else {
                    //on décolore le bouton 00h-23h
                    $(".fc-timeline24hCustomButton-button").removeClass('btn-success');
                    $(".fc-timeline24hCustomButton-button").removeClass('bg-success');
                    $(".fc-timeline24hCustomButton-button").addClass('btn-default');
                    $(".fc-timeline24hCustomButton-button").css('cursor', 'pointer');
                    $(".fc-timeline24hCustomButton-button").css('color', '');
                    //on autorise le click sur le bouton 00h-23h
                    //$(".fc-timeline24hCustomButton-button").removeAttr('disabled');
                    //on colore ON le bouton 04h-23h
                    $(".fc-timeline20hCustomButton-button").removeClass('btn-default');
                    $(".fc-timeline20hCustomButton-button").addClass('btn-success');
                    $(".fc-timeline20hCustomButton-button").addClass('bg-success');
                    $(".fc-timeline20hCustomButton-button").css('cursor', 'default');
                    $(".fc-timeline20hCustomButton-button").css('color', 'white');
                    //empéche le clic sur le bouton 00h-23h selectionné
                    //$(".fc-timeline20hCustomButton-button").attr('disabled','disabled');
                }
            }else{
                //on cache les boutons de redimensionnement du timeline
                $('.fc-timeline24hCustomButton-button').addClass('hide');
                $('.fc-timeline20hCustomButton-button').addClass('hide');
            }

            $(".fc-timeline24hCustomButton-button").html("<i class='glyphicon glyphicon-resize-full fa-lg'></i> 00h-23h");
            $(".fc-timeline20hCustomButton-button").html("<i class='glyphicon glyphicon-resize-small fa-lg'></i> 04h-23h");

            if(userisgranted == true) {

                $(".fc-interactionOnOffCustomButton-button").removeAttr('disabled');

                if (interactionsouris == false) {
                    $(".fc-interactionOnOffCustomButton-button").css('color','');
                    $(".fc-selectionEraserCustomButton-button").addClass('hide');
                    $(".fc-interactionOnOffCustomButton-button").removeClass('bg-success');
                    $(".fc-interactionOnOffCustomButton-button").html("<i class='glyphicon glyphicon-remove-sign fa-lg'></i> Sélection à la souris OFF");
                } else {
                    $(".fc-interactionOnOffCustomButton-button").css('color','white');
                    $(".fc-selectionEraserCustomButton-button").removeClass('hide');
                    $(".fc-interactionOnOffCustomButton-button").addClass('bg-success');
                    $(".fc-interactionOnOffCustomButton-button").html("<i class='glyphicon glyphicon-ok-sign fa-lg'></i> Sélection à la souris ON");
                }

            }else{

                $("#sw_ponctuelle").removeClass('bg-success');
                $("#sw_ponctuelle").css('color','#aaaaaa');
                $("#sw_ponctuelle").css('background-color','#dddddd');
                $("#sw_ponctuelle").css('cursor','default');
                $("#sw_saisoncourante").css('cursor','default');
                $("#sw_periode").css('cursor','default');

                $(".fc-interactionOnOffCustomButton-button").removeClass('bg-success');
                $(".fc-interactionOnOffCustomButton-button").css('color','#aaaaaa');
                $(".fc-interactionOnOffCustomButton-button").css('background-color','#dddddd');
                $(".fc-interactionOnOffCustomButton-button").css('cursor','default');
                $(".fc-interactionOnOffCustomButton-button").html("<i class='glyphicon glyphicon-remove-sign fa-lg' style='color:#aaaaaa;'></i> Sélection à la souris OFF");
                $(".fc-interactionOnOffCustomButton-button").unbind('click');

                $(".fc-selectionEraserCustomButton-button").addClass('hide');
                //$(".fc-interactionOnOffCustomButton-button").attr('disabled','disabled');

            }

            //on enleve la barre de scroll vertical parasite qui apparait parfois
            $(".fc-scroller-canvas").css("overflow-y","hidden");

            //message d'attente lors du chargement
            $("#mySpinnerModal").modal("show");

        },

        // cellules heure/vol
        dayRender: function (date, cell) {

            var jouraujourdhui = moment();
            var curentmoment = $('#calendar').fullCalendar('getDate');

            //couleur de fond des cellules du planning
            if(parseInt(date.format('H'))%2 == 0){

                if(moment(curentmoment.format('YYYY-MM-DD')).isSameOrAfter(jouraujourdhui.format('YYYY-MM-DD'))){
                    //si ce n'est pas un samedi ou un dimanche
                    if( (date.format('e') != 5) && (date.format('e') != 6) ) {
                        cell.css("background-color", couleursAlternativeFondPlanningJoursFuturs[0]);
                    }else {
                        //si c'est un samedi ou un dilmanche
                        cell.css("background-color", couleursAlternativeFondPlanningJoursWeekend[0]);
                    }
                }else{// dates passées du planning
                    cell.css("background-color", couleursAlternativeFondPlanningJoursPasses[0]);
                }

            }else{

                if(moment(curentmoment.format('YYYY-MM-DD')).isSameOrAfter(jouraujourdhui.format('YYYY-MM-DD'))){
                    //si ce n'est pas un samedi ou un dimanche
                    if( (date.format('e') != 5) && (date.format('e') != 6) ) {
                        cell.css("background-color", couleursAlternativeFondPlanningJoursFuturs[1]);
                    }else{
                        //si c'est un samedi ou un dilmanche
                        cell.css("background-color", couleursAlternativeFondPlanningJoursWeekend[1]);
                    }
                }else{// dates passées du planning
                    cell.css("background-color", couleursAlternativeFondPlanningJoursPasses[1]);
                }

            }

        },

        resourceLabelText: 'Avions / Heures',

        resources: function(callback) {

            var url = Routing.generate('planningvol_getavionsasjson');

            $.ajax({
                url: url,
                dataType: 'json',
                type: 'POST',
                success: function(doc) {

                    var jsonobjarray = $.parseJSON(doc);
                    lesresources = [];

                    jsonobjarray.forEach(function(uneresourcejson) {

                        lesresources.push({
                            id: uneresourcejson.id,
                            title: uneresourcejson.title,
                            type: uneresourcejson.type,
                            codecompagnie: uneresourcejson.compagnie
                        });

                    });

                    callback(lesresources);

                },
                error: function(){
                }

            });
        },


        resourceRender: function(resourceObj, labelTds, bodyTds) {
            labelTds.addClass('td_avion');
            labelTds.html('<div><div class="fc-cell-content"><span class="fc-expander-space"><span class="fc-icon"></span></span><span class="fc-cell-text"><i style="font-style:normal;font-weight:bold;font-size:1.0em;color:'+couleurTexteCellulesAvions+';">'+resourceObj.title+'</i><br/><i style="font-size:0.9em;color:'+couleurTexteCellulesAvions+';">'+resourceObj.type.split(" ")[0]+'</i></span></div></div>');
            if(resourceObj.codecompagnie == "XK"){ //avion appartenant à AirCorsica
                if(resourceObj.type == "AT7"){ //les AT7 d'AirCorsica
                    labelTds.css('background', couleurDeFondCellulesAvions_XK_AT7);
                }else{ //les autres avions d'AirCorsica A320
                    labelTds.css('background', couleurDeFondCellulesAvions_XK_320);
                }
            }else{ //avion affrété
                labelTds.css('background', couleurDeFondCellulesAvions_Affrete);
            }

        },



        events: function(start, end, timezone, callback) {

            var url = Routing.generate('planningvol_gettodayvolsasjson');

            $.ajax({
                url: url,
                dataType: 'json',
                type: 'POST',
                data: {
                    'start': start.unix(),
                    'end': end.unix()
                },
                success: function(doc) {
                    var jsonobjarray = $.parseJSON(doc);
                    leseventsdujourcourant = [];
                    var nbavionsimmobilises = 0;
                    var esteditable;

                    //valeur par defaut
                    affichageplanningavant4h = false;

                    jsonobjarray.forEach(function(uneventjson) {

                        if(uneventjson.immobilisation){

                            nbavionsimmobilises++;

                            leseventsdujourcourant.push({

                                id: uneventjson.id,
                                resourceId: uneventjson.resourceId,
                                start: uneventjson.start,
                                end: uneventjson.end,
                                immobilisation: uneventjson.immobilisation,
                                title: uneventjson.title,
                                causeimmobilisation: uneventjson.causeimmobilisation,
                                periodeimmobilisation: uneventjson.periodeimmobilisation,
                                textColor: uneventjson.textColor,
                                borderColor: uneventjson.borderColor,
                                className: uneventjson.className,
                                durationEditable: false,
                                overlap: false,
                                editable: false

                            });

                        }else {
                            var temp = uneventjson.start.split(" ");
                            var temp2 = temp[1].split(":");
                            var heurestart = parseInt(temp2[0]);
                            var dateStart = temp[0];
                            var dateStartMoment = moment(dateStart,"YYYY-MM-DD");
                            temp = uneventjson.end.split(" ");
                            temp2 = temp[1].split(":");
                            var endUnchanged = uneventjson.end;
                            var heureEnd = parseInt(temp2[0]);
                            var dateEnd = temp[0];
                            var dateEndMoment = moment(dateEnd,"YYYY-MM-DD");


                            if (interactionsouris == false){

                                esteditable = false;

                            }else{

                                /*if(uneventjson.msgEnvoye == 'true') {//on ne gére le déplacement à la souris que si le vol est acquité
                                    esteditable = true;
                                }else{
                                    esteditable = false;
                                }*/
                                esteditable = true;

                            }

                            if( (dateStartMoment.isBefore(dateEndMoment)) && (heureEnd < 4) && (uneventjson.chevauchejour == "matin") ){
                                affichageplanningavant4h = true; // on affiche un planning avec des tranche horaires de 0h -> 23h
                            }

                            if( dateEndMoment.isAfter($('#calendar').fullCalendar('getDate'))){
                                //permet d'empecher l'affichage de la partie d'un vol qui chevauche sur 2 jours aprés 23h59m59s
                                //l'argument end d'un event permet à fullcalendar de savoir ou arreter l'affichage de l'event
                                //on le stop arbitrairement à minuit, la valeur original est sauvé dans l'argument custom endUnchanged
                                // cette valeur custom sera utilisé dans la methode eventRender pour les calculs.
                                temp =  uneventjson.start.split(" ");
                                uneventjson.end = temp[0]+' 23:59:59';
                            }

                            leseventsdujourcourant.push({

                                id: uneventjson.id,
                                resourceId: uneventjson.resourceId,
                                start: uneventjson.start,
                                chevauchejour: uneventjson.chevauchejour,
                                end: uneventjson.end,
                                endUnchanged: endUnchanged,
                                villeDepart: uneventjson.villeDepart,
                                villeArrivee: uneventjson.villeArrivee,
                                codeVol: uneventjson.codeVol,
                                typeVol: uneventjson.typeVol,
                                title: uneventjson.title,
                                textColor: uneventjson.textColor,
                                backgroundColor: uneventjson.backgroundColor,
                                borderColor: uneventjson.borderColor,
                                durationEditable: uneventjson.durationEditable,
                                editable: esteditable,
                                avecUnAvionImmobilise: uneventjson.avecUnAvionImmobilise,
                                msgEnvoye: uneventjson.msgEnvoye,

                            });
                        }
                    });

                    if(typedeplanning==0) {
                        $(".fc-hebdomadaireCustomButton-button").removeClass('bg-success');
                        $(".fc-journalierCustomButton-button").addClass('bg-success');
                    }else{
                        $(".fc-hebdomadaireCustomButton-button").addClass('bg-success');
                        $(".fc-journalierCustomButton-button").removeClass('bg-success');
                    }

                    if(affichageplanningavant4h==false){

                        //on cache les boutons de redimensionnement du timeline
                        $('.fc-timeline24hCustomButton-button').removeClass('show');
                        $('.fc-timeline24hCustomButton-button').addClass('hide');
                        $('.fc-timeline20hCustomButton-button').removeClass('show');
                        $('.fc-timeline20hCustomButton-button').addClass('hide');

                    }else{
                        if (timelinesechellevisualisation == 0) {
                            //on décolore le bouton 04h-23h
                            $(".fc-timeline20hCustomButton-button").removeClass('bg-success');
                            //on autorise le click sur le bouton 04h-23h
                            $(".fc-timeline20hCustomButton-button").removeAttr('disabled');
                            //on colore ON le bouton 00h-23h
                            $(".fc-timeline24hCustomButton-button").addClass('bg-success');
                            //empéche le clic sur le bouton 04h-23h selectionné
                            $(".fc-timeline24hCustomButton-button").attr('disabled','disabled');

                        } else {
                            //on décolore le bouton 00h-23h
                            $(".fc-timeline24hCustomButton-button").removeClass('bg-success');
                            //on autorise le click sur le bouton 00h-23h
                            $(".fc-timeline24hCustomButton-button").removeAttr('disabled');
                            //on colore ON le bouton 04h-23h
                            $(".fc-timeline20hCustomButton-button").addClass('bg-success');
                            //empéche le clic sur le bouton 00h-23h selectionné
                            $(".fc-timeline20hCustomButton-button").attr('disabled','disabled');
                        }
                        //on affiche les boutons de redimensionnement du timeline
                        $('.fc-timeline24hCustomButton-button').removeClass('hide');
                        $('.fc-timeline24hCustomButton-button').addClass('show');
                        $('.fc-timeline20hCustomButton-button').removeClass('hide');
                        $('.fc-timeline20hCustomButton-button').addClass('show');

                    }

                    if((jsonobjarray.length==0)||(jsonobjarray.length == nbavionsimmobilises)){

                        affichageplanningavant4h = false;

                    }



                    callback(leseventsdujourcourant);
                },
                error: function(){
                }
            });
        },

        eventRender: function(event, element) {

            //on incremente le nombre d'event affiché pour pouvoir le comparer au contenu de l'array en mémoire
            var daystart = moment(event.start,'YYYY-MM-DD HH:mm:ss');
            var heuresdepart; if(daystart.hour()<10){heuresdepart='0'+daystart.hour();}else{heuresdepart = daystart.hour();}
            var minutesdepart; if(daystart.minute()<10){minutesdepart='0'+daystart.minute();}else{minutesdepart = daystart.minute();}

            //var dayend = moment(event.end,'YYYY-MM-DD HH:mm:ss');
            var dayend = moment(event.endUnchanged,'YYYY-MM-DD HH:mm:ss');
            var heuresarrivee; if(dayend.hour()<10){heuresarrivee='0'+dayend.hour();}else{heuresarrivee = dayend.hour();}
            var minutesarrivee; if(dayend.minute()<10){minutesarrivee='0'+dayend.minute();}else{minutesarrivee = dayend.minute();}

            // rajoute des petites fléche en haut et en bas d'un vol que l'on déplace verticalement
            var htmlDragTop = "";
            var htmlDragBottom = "";
            var htmlContent = "";

            //calcul de la durée du vol qui servira de test pour les tooltip des vols de courte durées
            var diffDureeVol = moment.duration(dayend.diff(daystart));//.humanize();
            var minutesDuVol = parseInt(diffDureeVol.asMinutes());

            if((eventbeingdragged!=null)&&(eventbeingdragged.id==event.id)){//l'event est déplacé verticalement

                //fleche vertical vers le haut
                htmlDragTop = '<div style="margin-top:-30px;text-align:center;padding-bottom:8px;"><i class="glyphicon glyphicon-arrow-up fa-lg" style="font-size:2.0em;color:#000000;"></i></div>';

                //fleche vertical vers le bas
                htmlDragBottom = '<div style="margin-bottom:0px;text-align:center;padding-top:0px;"><i class="glyphicon glyphicon-arrow-down fa-lg" style="font-size:2.0em;color:#000000;"></i></div>';
            }

            if((selectedeventsarray.indexOf(event.id)>=0)&&(eventbeingdragged==null)){//l'event est selectionné

                htmlDragTop = '<div style="margin-right:-10px;margin-top:-14px;text-align:right;margin-bottom:0px; height:0px;"><i class="glyphicon glyphicon-ok-circle fa-lg" style="z-index:10000;font-size:2.0em;color:'+couleurDeSelectionDesVols+';"></i></div>';
            }

            if(event.immobilisation){

                htmlContent = '<div class="fc-content" data-immo="true" style="margin-left:-2px; padding-right:4px; margin-top:-6px; background: repeating-linear-gradient(45deg,'+couleursAlternativeFondAvionImmobilise[0]+','+couleursAlternativeFondAvionImmobilise[0]+' 10px,'+couleursAlternativeFondAvionImmobilise[1]+' 10px,'+couleursAlternativeFondAvionImmobilise[1]+' 20px);';
                htmlContent = htmlContent+'cursor:default;';

                htmlContent = htmlContent+'">' +
                    '<div style="display: table; margin: 0 auto;"><div style="position:relative; top: 25%; margin:18px;">';
                for(var i=0;i<10;i++){
                    htmlContent = htmlContent+' <i class="glyphicon glyphicon-wrench fa-lg" style="font-size:1.5em;color:#FFFFFF;margin-left:29px;opacity:0.6;"></i>&nbsp;<span style="margin-top:2px;margin-right:29px;opacity:0.6;">'+event.causeimmobilisation+'</span>';
                }
                htmlContent = htmlContent+'</div></div></div>';

            }else{

                if((selectedeventsarray.indexOf(event.id)>=0)&&(eventbeingdragged==null)) {//l'event est selectionné
                    htmlContent = '<div class="fc-content un-event-tooltip" data-msgenvoye="'+event.msgEnvoye+'" data-idvol="'+event.id+'" data-immo="false" data-date="'+daystart.format('YYYY-MM-DD')+'" style="height:'+eval(Math.floor(defaultEventHeight+((defaultEventHeight*zoom_y)/10))-4)+'px;margin-left:-6px;margin-right:-3px;margin-top:'+eval((10-zoom_y)*3)+'px;border: 2px solid #000000;';
                }else if((eventbeingdragged!=null)&&(eventbeingdragged.id==event.id)) {//l'event est déplacé verticalement
                    htmlContent = '<div class="fc-content un-event-tooltip" data-msgenvoye="'+event.msgEnvoye+'" data-idvol="'+event.id+'" data-immo="false" data-date="'+daystart.format('YYYY-MM-DD')+'" style="height:'+eval(Math.floor(defaultEventHeight+((defaultEventHeight*zoom_y)/10))-4)+'px;background-color:'+event.backgroundColor+';margin-left:-6px;margin-right:-3px;margin-top:-8px;border: 2px solid #000000;';
                }else{
                    htmlContent = '<div class="fc-content un-event-tooltip" data-msgenvoye="'+event.msgEnvoye+'" data-idvol="'+event.id+'" data-immo="false" data-date="'+daystart.format('YYYY-MM-DD')+'" style="height:'+eval(Math.floor(defaultEventHeight+((defaultEventHeight*zoom_y)/10))-4)+'px;margin-left:-6px;margin-right:-3px;margin-top:-8px;border: 2px solid #000000;';
                }

                //forme du curseur en fonction des droits
                if (userisgranted == true){
                    /*if(event.msgEnvoye == 'true') {//message envoyé ou acquitté
                        htmlContent = htmlContent + 'cursor:pointer;';
                    }else{
                        htmlContent = htmlContent+'cursor:default;';
                    }*/
                    htmlContent = htmlContent + 'cursor:pointer;';

                }else{htmlContent = htmlContent+'cursor:default;';}
                htmlContent = htmlContent+'" ';

                // TOOLTIPS SUR LES VOLS EVENTS
                if(event.chevauchejour == "matin"){//tooltip affichant les infos des vols qui chevauche le matin (généralement il sont tromqués et illisibles)
                    //htmlContent = htmlContent + 'data-html="true" data-placement="right" title="'+event.villeDepart+'                  '+event.villeArrivee+'\n'+heuresdepart+':'+minutesdepart+'            '+heuresarrivee+':'+minutesarrivee+'\n'+daystart.format("DD")+daystart.format("MMM").charAt(0).toUpperCase()+daystart.format("MMM").substr(1, 3)+'         '+dayend.format("DD")+dayend.locale('fr').format("MMM").charAt(0).toUpperCase()+dayend.locale('fr').format("MMM").substr(1, 3)+'\n          '+event.codeVol+'\n            '+event.typeVol+'"';
                    htmlContent = htmlContent + 'data-html="true" data-placement="right"  title="<div style=\'width:100px;margin-left:-5px;margin-right:-5px;';
                    if(event.msgEnvoye == "true"){
                        htmlContent = htmlContent+'color:'+couleurTexteVolsAvecMsgEnvoye+';background-color:white;\'><div><div style=\'float:left;\'>&nbsp;<strong>' + event.villeDepart + '</strong></div><div style=\'float:right;\'><strong>' + event.villeArrivee + '</strong>&nbsp;</div></div><div style=\'clear:both;\'><div style=\'float:left;\'>&nbsp;' + heuresdepart + ':' + minutesdepart + '</div><div style=\'float:right;\'>' + heuresarrivee + ':' + minutesarrivee + '&nbsp;</div></div><div style=\'clear:both;\'><div style=\'float:left;\'>&nbsp;'+daystart.format("DD")+daystart.format("MMM").charAt(0).toUpperCase()+daystart.format("MMM").substr(1, 3)+'</div><div style=\'float:right;\'>'+dayend.format("DD")+dayend.locale('fr').format("MMM").charAt(0).toUpperCase()+dayend.locale('fr').format("MMM").substr(1, 3)+'&nbsp;</div></div><div style=\'clear:both;\'>          <strong>' + event.codeVol + '</strong></div><div><span style=\'border: 1px dotted black;background-color:'+event.backgroundColor+';padding-top:3px;padding-bottom:3px;float:left;width:30px;margin-left:15px;margin-right:10px;\'><strong>' + event.typeVol + '</strong></span><span class=\'fa-stack etat-send\' style=\'margin-bottom:2px;\'><i class=\'fa fa-envelope-o fa-stack-1x\'></i></span></div></div>"';
                    }else{
                        htmlContent = htmlContent+'color:'+couleurTexteVolsAvecMsgNonEnvoye+';background-color:white;\'><div><div style=\'float:left;\'>&nbsp;<strong>' + event.villeDepart + '</strong></div><div style=\'float:right;\'><strong>' + event.villeArrivee + '</strong>&nbsp;</div></div><div style=\'clear:both;\'><div style=\'float:left;\'>&nbsp;' + heuresdepart + ':' + minutesdepart + '</div><div style=\'float:right;\'>' + heuresarrivee + ':' + minutesarrivee + '&nbsp;</div></div><div style=\'clear:both;\'><div style=\'float:left;\'>&nbsp;'+daystart.format("DD")+daystart.format("MMM").charAt(0).toUpperCase()+daystart.format("MMM").substr(1, 3)+'</div><div style=\'float:right;\'>'+dayend.format("DD")+dayend.locale('fr').format("MMM").charAt(0).toUpperCase()+dayend.locale('fr').format("MMM").substr(1, 3)+'&nbsp;</div></div><div style=\'clear:both;\'>          <strong>' + event.codeVol + '</strong></div><div><span style=\'border: 1px dotted black;background-color:'+event.backgroundColor+';padding-top:3px;padding-bottom:3px;float:left;width:30px;margin-left:12px;margin-right:10px;\'><strong>' + event.typeVol + '</strong></span><span class=\'fa-stack etat-send\' style=\'margin-bottom:2px;\'><i class=\'fa fa-envelope-o fa-stack-1x\'></i><i class=\'fa fa-ban fa-stack-2x text-danger\' style=\'font-size:2.3em;opacity:0.65;\'></i></span></div></div>"';
                    }
                }else if(event.chevauchejour == "soir"){//tooltip affichant les infos des vols qui chevauche le soir(généralement il sont tromqués et illisibles)
                    //htmlContent = htmlContent + 'data-html="true" data-placement="left"  title="'+event.villeDepart+'                  '+event.villeArrivee+'\n'+heuresdepart+':'+minutesdepart+'            '+heuresarrivee+':'+minutesarrivee+'\n'+daystart.format("DD")+daystart.format("MMM").charAt(0).toUpperCase()+daystart.format("MMM").substr(1, 3)+'         '+dayend.format("DD")+dayend.locale('fr').format("MMM").charAt(0).toUpperCase()+dayend.locale('fr').format("MMM").substr(1, 3)+'\n          '+event.codeVol+'\n            '+event.typeVol+'"';
                    htmlContent = htmlContent + 'data-html="true" data-placement="left"  title="<div style=\'width:100px;margin-left:-5px;margin-right:-5px;';
                    if(event.msgEnvoye == "true"){
                        htmlContent = htmlContent+'color:'+couleurTexteVolsAvecMsgEnvoye+';background-color:white;\'><div><div style=\'float:left;\'>&nbsp;<strong>' + event.villeDepart + '</strong></div><div style=\'float:right;\'><strong>' + event.villeArrivee + '</strong>&nbsp;</div></div><div style=\'clear:both;\'><div style=\'float:left;\'>&nbsp;' + heuresdepart + ':' + minutesdepart + '</div><div style=\'float:right;\'>' + heuresarrivee + ':' + minutesarrivee + '&nbsp;</div></div><div style=\'clear:both;\'><div style=\'float:left;\'>&nbsp;'+daystart.format("DD")+daystart.format("MMM").charAt(0).toUpperCase()+daystart.format("MMM").substr(1, 3)+'</div><div style=\'float:right;\'>'+dayend.format("DD")+dayend.locale('fr').format("MMM").charAt(0).toUpperCase()+dayend.locale('fr').format("MMM").substr(1, 3)+'&nbsp;</div></div><div style=\'clear:both;\'>          <strong>' + event.codeVol + '</strong></div><div><span style=\'border: 1px dotted black;background-color:'+event.backgroundColor+';padding-top:3px;padding-bottom:3px;float:left;width:30px;margin-left:15px;margin-right:10px;\'><strong>' + event.typeVol + '</strong></span><span class=\'fa-stack etat-send\' style=\'margin-bottom:2px;\'><i class=\'fa fa-envelope-o fa-stack-1x\'></i></span></div></div>"';
                    }else{
                        htmlContent = htmlContent+'color:'+couleurTexteVolsAvecMsgNonEnvoye+';background-color:white;\'><div><div style=\'float:left;\'>&nbsp;<strong>' + event.villeDepart + '</strong></div><div style=\'float:right;\'><strong>' + event.villeArrivee + '</strong>&nbsp;</div></div><div style=\'clear:both;\'><div style=\'float:left;\'>&nbsp;' + heuresdepart + ':' + minutesdepart + '</div><div style=\'float:right;\'>' + heuresarrivee + ':' + minutesarrivee + '&nbsp;</div></div><div style=\'clear:both;\'><div style=\'float:left;\'>&nbsp;'+daystart.format("DD")+daystart.format("MMM").charAt(0).toUpperCase()+daystart.format("MMM").substr(1, 3)+'</div><div style=\'float:right;\'>'+dayend.format("DD")+dayend.locale('fr').format("MMM").charAt(0).toUpperCase()+dayend.locale('fr').format("MMM").substr(1, 3)+'&nbsp;</div></div><div style=\'clear:both;\'>          <strong>' + event.codeVol + '</strong></div><div><span style=\'border: 1px dotted black;background-color:'+event.backgroundColor+';padding-top:3px;padding-bottom:3px;float:left;width:30px;margin-left:12px;margin-right:10px;\'><strong>' + event.typeVol + '</strong></span><span class=\'fa-stack etat-send\' style=\'margin-bottom:2px;\'><i class=\'fa fa-envelope-o fa-stack-1x\'></i><i class=\'fa fa-ban fa-stack-2x text-danger\' style=\'font-size:2.3em;opacity:0.65;\'></i></span></div></div>"';
                    }
                }else{//tooltip affichant les infos des vols
                    if(minutesDuVol<50) {
                        htmlContent = htmlContent + 'data-html="true" data-placement="bottom"  title="<div style=\'width:80px;margin-left:-5px;margin-right:-5px;';
                        if(event.msgEnvoye == "true"){
                            htmlContent = htmlContent+'color:'+couleurTexteVolsAvecMsgEnvoye+';background-color:white;\'><div><div style=\'float:left;\'>&nbsp;<strong>' + event.villeDepart + '</strong></div><div style=\'float:right;\'><strong>' + event.villeArrivee + '</strong>&nbsp;</div></div><div style=\'clear:both;\'><div style=\'float:left;\'>&nbsp;' + heuresdepart + ':' + minutesdepart + '</div><div style=\'float:right;\'>' + heuresarrivee + ':' + minutesarrivee + '&nbsp;</div></div><div style=\'clear:both;\'>          <strong>' + event.codeVol + '</strong></div><div><span style=\'border: 1px dotted black;background-color:'+event.backgroundColor+';padding-top:3px;padding-bottom:3px;float:left;width:30px;margin-left:10px;margin-right:10px;\'><strong>' + event.typeVol + '</strong></span><span class=\'fa-stack etat-send\' style=\'margin-bottom:2px;\'><i class=\'fa fa-envelope-o fa-stack-1x\'></i></span></div></div>"';
                        }else{
                            htmlContent = htmlContent+'color:'+couleurTexteVolsAvecMsgNonEnvoye+';background-color:white;\'><div><div style=\'float:left;\'>&nbsp;<strong>' + event.villeDepart + '</strong></div><div style=\'float:right;\'><strong>' + event.villeArrivee + '</strong>&nbsp;</div></div><div style=\'clear:both;\'><div style=\'float:left;\'>&nbsp;' + heuresdepart + ':' + minutesdepart + '</div><div style=\'float:right;\'>' + heuresarrivee + ':' + minutesarrivee + '&nbsp;</div></div><div style=\'clear:both;\'>          <strong>' + event.codeVol + '</strong></div><div><span style=\'border: 1px dotted black;background-color:'+event.backgroundColor+';padding-top:3px;padding-bottom:3px;float:left;width:30px;margin-left:7px;margin-right:10px;\'><strong>' + event.typeVol + '</strong></span><span class=\'fa-stack etat-send\' style=\'margin-bottom:2px;\'><i class=\'fa fa-envelope-o fa-stack-1x\'></i><i class=\'fa fa-ban fa-stack-2x text-danger\' style=\'font-size:2.3em;opacity:0.65;\'></i></span></div></div>"';
                        }
                    }
                }

                htmlContent = htmlContent + '>';

                htmlContent = htmlContent+'<div style="position:relative;height:50%;z-index:10000;background-color:'+event.backgroundColor+';">';

                //Infos départ
                htmlContent = htmlContent+'<div id="contenuDuVol_'+event.id+'" class="contenuDuVol" style="position:absolute; left:0em;';
                if(event.msgEnvoye == "true"){
                    htmlContent = htmlContent+'color:'+couleurTexteVolsAvecMsgEnvoye+';';
                }else{
                    htmlContent = htmlContent+'color:'+couleurTexteVolsAvecMsgNonEnvoye+';';
                }
                htmlContent = htmlContent+'" >';
                htmlContent = htmlContent+'<p class="libVille" style="font-size:0.95em;font-weight:bold;margin-left:1px;text-align: left;margin:0px;">'+event.villeDepart+'</p>' +
                    '<p class="libHoraire" style="font-size:0.85em;text-align: left;margin:0px;margin-top:-1px;">'+heuresdepart+':'+minutesdepart+'</p>' +
                    '</div>';

                //Infos arrivée
                htmlContent = htmlContent+'<div class="contenuDuVol" style="position:absolute; right:0em;';
                if(event.msgEnvoye == "true"){
                    htmlContent = htmlContent+'color:'+couleurTexteVolsAvecMsgEnvoye+';';
                }else{
                    htmlContent = htmlContent+'color:'+couleurTexteVolsAvecMsgNonEnvoye+';';
                }
                htmlContent = htmlContent+'" >';
                htmlContent = htmlContent+'<p class="libVille" style="font-size:0.95em;font-weight:bold;margin-right:1px;text-align: right;margin:0px;">'+event.villeArrivee+'</p>' +
                    '<p class="libHoraire" style="font-size:0.85em;text-align: right;margin:0px;margin-top:-1px;">'+heuresarrivee+':'+minutesarrivee+'</p>' +
                    '</div>';

                htmlContent = htmlContent+'</div>';

                //Infos codeVol et typeVol
                htmlContent = htmlContent+'<div style="position:relative;height:'+Math.floor((defaultEventHeight+((defaultEventHeight*zoom_y)/10))/2)+'px;margin-top:-1px;background-color:'+event.backgroundColor+';';
                if(event.msgEnvoye == "true"){
                    htmlContent = htmlContent+'color:'+couleurTexteVolsAvecMsgEnvoye+';';
                }else{
                    htmlContent = htmlContent+'color:'+couleurTexteVolsAvecMsgNonEnvoye+';';
                }
                htmlContent = htmlContent+'" >';
                htmlContent = htmlContent+'<p style="font-size:0.85em;text-align: center;margin:1px; padding:0px;font-weight:bold;">'+event.codeVol+'</p><p style="font-size:0.85em;text-align: center;margin-top:-2px;padding:0px;">'+event.typeVol+'</p></div>';

                htmlContent = htmlContent+'</div>';

            }

            element.html(htmlDragTop + htmlContent +  htmlDragBottom);

        },
        eventAfterAllRender: function( view ) {

            //set les customs tooltips
            $('.un-event-tooltip').tooltip({
                container: 'body',
                delay: {show: 1000, hide: 100}
            });

            //lors du chargement rechargement de la page vérification que lle planning hebdomadaire n'était pas déja ouvert
            if( (loadpage == true) && (typedeplanning == 1) ){
                loadpage = false; //ne doit s'executer qu'au chargement ou au refresh de la page

                //on deselectionne tous les jour de la semaines du planning hebdomadaire
                $('div[id^="PlHebd_"]').each(function(){
                    $(this).removeClass('badge');
                    $(this).removeClass('badge-success');
                });
                //on selectionne le jour de la semaine du planning hebdomadaire
                var momtmp = $('#calendar').fullCalendar('getDate');
                $("#PlHebd_"+momtmp.format('e')).addClass('badge');
                $("#PlHebd_"+momtmp.format('e')).addClass('badge-success');

                $("#bloccalendrierhebdomadaire").css("display","");
                $('#leplanninghebdomadaire').css('opacity','0.0');

                selectedeventsarray = [];

                //on met a jour la var de session
                var curentmoment = $('#calendar').fullCalendar('getDate');
                typedeplanning = 1;
                majPlanningVolParameters(interactionsouris,selectedeventsarray,curentmoment.format('DD-MM-YYYY'),zoom_y,typedemodificationplanning,typedeplanning,periodecustomdebut,periodecustomfin,selectedIdSaison);

                $(".fc-hebdomadaireCustomButton-button").addClass('bg-success');
                $(".fc-journalierCustomButton-button").removeClass('bg-success');

                //on lance l'affichage du résumé de la semaine (preview des planning de lundi à dimanche)
                destroyAllHebdomadaireCaldendars();

                $('div[id^="PlHebd_"]').each(function(){
                    //$(this).css("color","");
                    $(this).removeClass('badge');
                    $(this).removeClass('badge-success');
                });

                $("#bloccalendrierhebdomadaire").css("display","");
                $('#leplanninghebdomadaire').css('opacity','0.0');

                createAllHebdomadaireCaldendars(curentmoment);

            }
            if(affichageplanningavant4h == true) { //affichage des boutons d'interfaces

                if (timelinesechellevisualisation == 0) {

                    if(firsttimelineplanningrendering == false) {

                        firsttimelineplanningrendering = true;
                        $('#calendar').fullCalendar('option', 'minTime', '00:00'); //on modifie la timeline

                    }

                } else {

                    if(firsttimelineplanningrendering==false) {

                        firsttimelineplanningrendering = true;
                        $('#calendar').fullCalendar('option', 'minTime', '04:00'); //on modifie la timeline

                    }

                }
            }else{
                if(firsttimelineplanningrendering==false) {
                    firsttimelineplanningrendering = true;
                    $('#calendar').fullCalendar('option', 'minTime', '04:00'); //on modifie la timeline
                }
            }

            //cas d'un event sur un avion immobilisé (vol d'essai par exemple)
            aJQueryElementsVolsAvecUnAvionImmobilise.forEach(function(element) {
                //on le positionne sur l'avion immobilisé
                element.css('top','0px');
                element.css('z-index','1000');
            });
            aJQueryElementsVolsAvecUnAvionImmobilise = new Array();

            //on enléve le message d'attente
            $("#mySpinnerModal").modal("hide");

            //le planning à finis de s'afficher
            loadpage = false;

        },
        eventAfterRender: function( event, element, view ) {

            //cas d'un event sur un avion immobilisé (vol d'essai par exemple)
            if(event.avecUnAvionImmobilise == "true"){
                //on verifie que l'element jquery ne se trouve pas déja dans le tableau
                var elementjquerydejaenregistrerdansletableau = false;
                for (var i = 0; i < aJQueryElementsVolsAvecUnAvionImmobilise.length; i++) {
                    if (aJQueryElementsVolsAvecUnAvionImmobilise[i] === element) {
                        elementjquerydejaenregistrerdansletableau = true;
                        break;
                    }
                }
                if(elementjquerydejaenregistrerdansletableau == false){
                    aJQueryElementsVolsAvecUnAvionImmobilise.push(element);
                }
            }

            //oblige les lignes d'avion à avoir toujours la même hauteur, même en cas d'enregistrement de vol éroné (même heure)
            $("#calendar .fc-content table tbody tr td").children().css("height",eval(Math.floor(defaultEventHeight+((defaultEventHeight*zoom_y)/10))-4)+"px");

        },
        eventClick: function( event, jsEvent, view ) {

            //on cache les tooltips pour éviter un bug
            $('.un-event-tooltip').tooltip("hide");

            if(userisgranted == true) {

                if (event.immobilisation) {//on ne gére pas le click ici car il ne sagit pas de vol mais d'une période de non disponibilité d'un avion

                    // Nouvelle fonctionnalité à discuter de son utilité avec AIR CORSICA, info sur l'immobilisation d'un avion

                }  else {

                    //if(event.msgEnvoye == 'true') {//message envoyé ou acquitté

                        if (interactionsouris == false) {

                            //eventbeingdragged = null;
                            //
                            ////var unidvol;
                            //var unvol;
                            //var temparray = selectedeventsarray;
                            ////on vide le tableau la modif viens d'étre effectuee
                            //selectedeventsarray = [];
                            //
                            ////on met a jour la var de session
                            //var curentmoment = $('#calendar').fullCalendar('getDate');
                            //majPlanningVolParameters(interactionsouris, selectedeventsarray, curentmoment.format('DD-MM-YYYY'), zoom_y, typedemodificationplanning, typedeplanning,periodecustomdebut,periodecustomfin,selectedIdSaison);
                            //
                            ////on fais un rendu des element qu'il y avait dans le tableau des selection (copie temparray) pour effacer le symbole de selection
                            //temparray.forEach(function (unidvol) {
                            //
                            //    //on récuoére l'eventl
                            //    unvol = $("#calendar").fullCalendar('clientEvents', unidvol); //clientEvents retourne un tableau avec un seul élément
                            //
                            //    //on rafraichi l'event
                            //    $('#calendar').fullCalendar('updateEvent', unvol[0]);
                            //
                            //});
                            //
                            //$.ajax({
                            //    async: true,   // enlève l'erreur 500 (Internal Server Error) au retour de l'ajax
                            //    type: "GET",
                            //    url: Routing.generate('vol_modal',{id: event.id}),
                            //    success: function (data) {
                            //        $('#myModal .modal-body').html(data);
                            //    },
                            //    complete: function () {
                            //        $('#form_vol_edit .dropdown-toggle').dropdown();
                            //    },
                            //    beforeSend: function () {
                            //        $('#myModal').unbind();
                            //        $('#myModal').modal('toggle');
                            //        $('#myModal .modal-body').html("<div class='progress' id='loadingDiv'><div class='progress-bar progress-bar-striped progress-bar-success active' role='progressbar' \n\
                            //                 aria-valuenow='100' aria-valuemin='0' aria-valuemax='100' style='width: 100%'></div></div>");
                            //        //$('#myModal .close').on('click', function () {
                            //        //    window.location.reload();
                            //        //});
                            //    }
                            //});
                            //return false;


                            // e.preventDefault();
                            var current_route = $(this).data('route');
                            $.ajax({
                                async: true,
                                type: "GET",
                                url: Routing.generate('vol_modal', {id: event.id}),
                                success: function (data) {
                                    $('#myModal .modal-body').html(data);
                                    bindSubmitFormVol();
                                },
                                complete: function () {
                                    $('#route_to_redirect').val(current_route);
                                    $('#form_vol_edit .dropdown-toggle').dropdown();
                                    //$('#aircorsica_xkplanbundle_vol_aeroport_depart').select2('open');
                                },
                                beforeSend: function () {
                                    $('#myModal').attr('data-backdrop', 'static');//empeche de fermer la fenetre en cliquant à l'exterieur du modal
                                    $('#myModal').modal('toggle');
                                    $('#myModal .modal-body').html("<div class='progress' id='loadingDiv'><div class='progress-bar progress-bar-striped progress-bar-success active' role='progressbar' aria-valuenow='100' aria-valuemin='0' aria-valuemax='100' style='width: 100%;'></div></div>");
                                    $('#myModal .close').on('click', function () {
                                        //window.location.reload();
                                        $('#myModal').on('hidden.bs.modal', function () {
                                            //enleve le bug des datepicker qui sont inactifs à la sortie du modal
                                            resetDatePickerWhenExitingAjaxLoadedModal();
                                        });
                                    });
                                }
                            });


                        } else {

                            /*if (event.msgEnvoye == 'true') {//on ne gére le click que si le vol est acquité */

                                if (typedeplanning == 1) {
                                    var selectiondejours = 0;
                                    $('div[id^="PlHebd_"]').each(function () {
                                        if ($(this).hasClass('joursem_sel')) {
                                            selectiondejours++;
                                        }
                                    });
                                }

                                if ((typedeplanning == 0) || ( (typedeplanning == 1) && (selectiondejours > 0) )) {

                                    eventbeingdragged = null;

                                    if (selectedeventsarray.indexOf(event.id) >= 0) { //si l'event existe deja on l'enléve

                                        //on enléve l'élément
                                        selectedeventsarray = selectedeventsarray.filter(function (item) {
                                            return item !== event.id;
                                        });

                                        //maj de l'event
                                        event.className = "";

                                        //on met a jour la var de session
                                        var curentmoment = $('#calendar').fullCalendar('getDate');
                                        majPlanningVolParameters(interactionsouris, selectedeventsarray, curentmoment.format('DD-MM-YYYY'), zoom_y, typedemodificationplanning, typedeplanning, periodecustomdebut, periodecustomfin, selectedIdSaison);


                                    } else { //on le rajoute

                                        var dernierEventSelectionne = $("#calendar").fullCalendar('clientEvents', selectedeventsarray[selectedeventsarray.length - 1]);//on recupére l'event précedent du tableau des event selectionnés

                                        if (selectedeventsarray.length == 0) {//si le tableau est vide

                                            //on rajoute le vol
                                            selectedeventsarray.push(event.id);
                                            //on met a jour la var de session
                                            var curentmoment = $('#calendar').fullCalendar('getDate');
                                            majPlanningVolParameters(interactionsouris, selectedeventsarray, curentmoment.format('DD-MM-YYYY'), zoom_y, typedemodificationplanning, typedeplanning, periodecustomdebut, periodecustomfin, selectedIdSaison);

                                        } else {//quelque soit le type d'appareil

                                            //on rajoute le vol
                                            selectedeventsarray.push(event.id);

                                            //on met a jour la var de session
                                            var curentmoment = $('#calendar').fullCalendar('getDate');
                                            majPlanningVolParameters(interactionsouris, selectedeventsarray, curentmoment.format('DD-MM-YYYY'), zoom_y, typedemodificationplanning, typedeplanning, periodecustomdebut, periodecustomfin, selectedIdSaison);


                                        }

                                    }

                                } else {
                                    bootbox.alert("<i class='glyphicon glyphicon-alert' style='color: red;'></i> Attention, en mode hebdomadaire, vous devez d\'abords choisir au moins un jour avant de selectionner les vols!");
                                }


                                //on rafraichi l'event selectionné/ ou deselectionné
                                $('#calendar').fullCalendar('renderEvent', event);


                            //}

                        }

                    //}

                }

            }

        },
        eventDragStart: function( event, jsEvent, ui, view ) {

            eventbeingdragged = event;
            startresourceeventbeingdragged = $('#calendar').fullCalendar( 'getResourceById', event.resourceId );

            interdictionDragStart = false;
            libelleErreurInterdictionDragStart = "";

            /*if(event.msgEnvoye == 'true') {//message envoyé ou acquitté */
                event.editable = true;


                // on n'a pas le droit de déplacer un event seul sans qu'une date ne soit selectionné si on est en mode résumé hebdomadaire
                if (typedeplanning == 1) { //Plannings Hebdomadaire Visible

                    var selectiondejours = 0;
                    $('div[id^="PlHebd_"]').each(function () {
                        if ($(this).hasClass('joursem_sel')) {
                            selectiondejours++;
                        }
                    });

                    //Modification Ponctuelle et aucun jours n'est selectionné
                    if ( (typedemodificationplanning == 2) && (selectiondejours == 0) ) {//erreur: aucun jours n'est selectionné en mode hebdomadaire

                        //vérification des dates des périodes
                        if( ($("#periodecustomdatedebut").val() != "") && ($("#periodecustomdatefin").val() != "") ){ //les 2 periodes sont saisies

                            var periodecustomdatedebut = moment($("#periodecustomdatedebut").val(), "DD-MM-YYYY");
                            var periodecustomdatefin = moment($("#periodecustomdatefin").val(), "DD-MM-YYYY");

                            if( periodecustomdatefin.isBefore(periodecustomdatedebut, 'day') ){
                                libelleErreurInterdictionDragStart = "Attention, en mode hebdomadaire, vous devez d'abords choisir au moins un jour avant de selectionner les vols.\nDe plus, la date de début de la période personnalidable est postérieur à la date de fin.";
                            }else{
                                libelleErreurInterdictionDragStart = "Attention, en mode hebdomadaire, vous devez d'abords choisir au moins un jour avant de selectionner les vols.";
                            }

                        }else{ //erreur: une des 2 période n'est pas définie

                            libelleErreurInterdictionDragStart = "Attention, en mode hebdomadaire, vous devez d'abords choisir au moins un jour avant de selectionner les vols.\nDe plus, au moins une des dates de la période personnalidable est vide.";
                        }

                        //on met le flag à true
                        interdictionDragStart = true;

                        //on désactive l'event (plus le droit de le déplacer)
                        event.editable = false;

                        //on vide le tableau la modif viens d'étre effectuee
                        selectedeventsarray = [];
                        eventbeingdragged = null;

                        //on met a jour la var de session
                        var curentmoment = $('#calendar').fullCalendar('getDate');
                        majPlanningVolParameters(interactionsouris,selectedeventsarray,curentmoment.format('DD-MM-YYYY'),zoom_y,typedemodificationplanning,typedeplanning,periodecustomdebut,periodecustomfin,selectedIdSaison);

                        //Modification Ponctuelle et un/des jour(s) sont selectionnés
                    }else if (typedemodificationplanning == 2) {//période personnalisable

                        //vérification des dates des périodes
                        if( ($("#periodecustomdatedebut").val() != "") && ($("#periodecustomdatefin").val() != "") ){ //les 2 periodes sont saisies

                            var periodecustomdatedebut = moment($("#periodecustomdatedebut").val(), "DD-MM-YYYY");
                            var periodecustomdatefin = moment($("#periodecustomdatefin").val(), "DD-MM-YYYY");

                            if( periodecustomdatefin.isBefore(periodecustomdatedebut, 'day') ){

                                libelleErreurInterdictionDragStart = "Attention,  la date de début de la période personnalidable est postérieur à la date de fin, corrigé les dates.";

                                //on met le flag à true
                                interdictionDragStart = true;

                                //on désactive l'event (plus le droit de le déplacer)
                                event.editable = false;

                                //on vide le tableau la modif viens d'étre effectuee
                                selectedeventsarray = [];
                                eventbeingdragged = null;

                                //on met a jour la var de session
                                var curentmoment = $('#calendar').fullCalendar('getDate');
                                majPlanningVolParameters(interactionsouris,selectedeventsarray,curentmoment.format('DD-MM-YYYY'),zoom_y,typedemodificationplanning,typedeplanning,periodecustomdebut,periodecustomfin,selectedIdSaison);

                            }

                        }else{ //erreur une des 2 période n'est pas définie

                            libelleErreurInterdictionDragStart = "Attention, au moins une des dates de la période personnalidable est vide, saisissez les dates.";

                            //on met le flag à true
                            interdictionDragStart = true;

                            //on désactive l'event (plus le droit de le déplacer)
                            event.editable = false;

                            //on vide le tableau la modif viens d'étre effectuee
                            selectedeventsarray = [];
                            eventbeingdragged = null;

                            //on met a jour la var de session
                            var curentmoment = $('#calendar').fullCalendar('getDate');
                            majPlanningVolParameters(interactionsouris,selectedeventsarray,curentmoment.format('DD-MM-YYYY'),zoom_y,typedemodificationplanning,typedeplanning,periodecustomdebut,periodecustomfin,selectedIdSaison);

                        }

                        //aucun jours n'est selectionné en mode hebdomadaire
                    }else if (selectiondejours == 0) {//pas de jours selectionnés dans le planning hebdomadaire

                        libelleErreurInterdictionDragStart = "Attention, en mode hebdomadaire, vous devez d'abords choisir au moins un jour avant de selectionner les vols.";

                        //on met le flag à true
                        interdictionDragStart = true;

                        //on désactive l'event (plus le droit de le déplacer)
                        event.editable = false;

                        //on vide le tableau la modif viens d'étre effectuee
                        selectedeventsarray = [];
                        eventbeingdragged = null;

                        //on met a jour la var de session
                        var curentmoment = $('#calendar').fullCalendar('getDate');
                        majPlanningVolParameters(interactionsouris,selectedeventsarray,curentmoment.format('DD-MM-YYYY'),zoom_y,typedemodificationplanning,typedeplanning,periodecustomdebut,periodecustomfin,selectedIdSaison);

                    }

                }else{ //Planning Journalier (Plannings Hebdomadaire cachés)

                    if (typedemodificationplanning == 2) {
                        //vérification des dates des périodes
                        if( ($("#periodecustomdatedebut").val() != "") && ($("#periodecustomdatefin").val() != "") ){ //les 2 periodes sont saisies

                            var periodecustomdatedebut = moment($("#periodecustomdatedebut").val(), "DD-MM-YYYY");
                            var periodecustomdatefin = moment($("#periodecustomdatefin").val(), "DD-MM-YYYY");

                            if( periodecustomdatefin.isBefore(periodecustomdatedebut, 'day') ){

                                libelleErreurInterdictionDragStart = "Attention,  la date de début de la période personnalidable est postérieur à la date de fin, corrigez les dates.";

                                //on met le flag à true
                                interdictionDragStart = true;

                                //on désactive l'event (plus le droit de le déplacer)
                                event.editable = false;

                                //on vide le tableau la modif viens d'étre effectuee
                                selectedeventsarray = [];
                                eventbeingdragged = null;

                                //on met a jour la var de session
                                var curentmoment = $('#calendar').fullCalendar('getDate');
                                majPlanningVolParameters(interactionsouris,selectedeventsarray,curentmoment.format('DD-MM-YYYY'),zoom_y,typedemodificationplanning,typedeplanning,periodecustomdebut,periodecustomfin,selectedIdSaison);

                            }

                        }else{ //erreur une des 2 période n'est pas définie

                            libelleErreurInterdictionDragStart = "Attention, au moins une des dates de la période personnalidable est vide, saisissez les dates.";

                            //on met le flag à true
                            interdictionDragStart = true;

                            //on désactive l'event (plus le droit de le déplacer)
                            event.editable = false;

                            //on vide le tableau la modif viens d'étre effectuee
                            selectedeventsarray = [];
                            eventbeingdragged = null;

                            //on met a jour la var de session
                            var curentmoment = $('#calendar').fullCalendar('getDate');
                            majPlanningVolParameters(interactionsouris,selectedeventsarray,curentmoment.format('DD-MM-YYYY'),zoom_y,typedemodificationplanning,typedeplanning,periodecustomdebut,periodecustomfin,selectedIdSaison);

                        }
                    }

                }

            /*}else{
                //on désactive l'event (plus le droit de le déplacer)
                event.editable = false;
                eventbeingdragged = null;
            }*/

        },
        eventDrop: function( event, delta, revertFunc ) {

            //on verifie qu'on ai bien le droit de déplacer un élément
            if (interdictionDragStart == false) {

                //elementbeingdragged = null;
                eventbeingdragged = null;

                var newressource = $('#calendar').fullCalendar('getResourceById', event.resourceId);
                var listedescodesvoldelalert = '';
                var listedesavionsdelalert = '';
                var warningambiguousavionsdelalert = '';
                var warningrappelaverifierdelalert = '';
                var unvol;
                var unavion;
                var aoldressources = new Array();

                // récupération de la liste des vols à modifier
                // --------------------------------------------
                var multiDropAllowedForSelection = true;
                if (selectedeventsarray.length == 0) { //pas de multiselection, il n'y a qu'un seul vol (celui déplacé)
                    selectedeventsarray.push(event.id);
                }
                selectedeventsarray.forEach(function (unidvol) {

                    unvol = $("#calendar").fullCalendar('clientEvents', unidvol); //clientEvents retourne un tableau avec un seul élément
                    listedescodesvoldelalert += ' ' + unvol[0].codeVol + ','; //on rajoute le code vol au texte des codevol modifiée

                    unavion = $("#calendar").fullCalendar('getEventResource', unidvol);

                    if (unidvol != event.id) {//si ce n'est pas l'event que l'on déplace avec la souris

                        if (listedesavionsdelalert.indexOf(unavion.title) ==-1) {//l'avion n'existe pas déja dans la liste
                            listedesavionsdelalert += ' ' + unavion.title + ','; //on rajoute l'avion d'origine au texte des avions modifiée
                            aoldressources.push(unavion);
                        }
                        // Vérification de la faisabilité de cette modification pour le jour courant (affichage)
                        // Pour les modification hebdomadaire ou sur une saison c'est le retour de la méthode ajax qui donnera une erreur ou la réalisation des modifs
                        // pour les events(vols) que l'on déplace non géré par le fullcalendar
                        // c'est à dire tous mis à  part celui qu'on deplace effectivement(petites fleches)

                        if (allowDropOfOneParticularMultiselectEvent(unvol[0], newressource, event) == false) {
                            multiDropAllowedForSelection = false;
                        }
                    }

                });
                if (listedesavionsdelalert.indexOf(startresourceeventbeingdragged.title) ==-1) {//l'avion n'existe pas déja dans la liste
                    listedesavionsdelalert += ' ' + startresourceeventbeingdragged.title; //on rajoute l'avion d'origine de l'element déplacé
                    aoldressources.push(startresourceeventbeingdragged);
                }
                listedescodesvoldelalert = listedescodesvoldelalert.substr(0, listedescodesvoldelalert.length - 1); //on enléve la derniére virgule et l'espace de fin

                // Autre verification de la faisbilité de cette modification pour le jour courant (affichage)
                // Est-ce que l'avion d'origine est du même type que l'avion cible
                if (isAmbiguousRessourceTarget(aoldressources, newressource) == false) {
                    //multiDropAllowedForSelection = false;
                    warningambiguousavionsdelalert = " <br><br><strong>Anomalie possible:</strong> un(des) vol(s) déplacé provient d\'un avion de type différent que l\'avion cible !<br>";
                }

                // Autre rappel: vérifeir que ces pré-requis sont valide avant de lancer la modification
                warningrappelaverifierdelalert = " <br><br><strong>Rappel:</strong> les pré-requis suivants sont a vérifier avant de lancer cette modification, <br/>Cliquez ici pour les consulter: <i id='showrappelsaverifer' class='glyphicon glyphicon-info-sign fa-lg'></i><br><br>" +
                                                                         "<ul id='rappelsaverifier' class='hide'><li><i>obligation pour les vols source d’avoir leurs messages préalablement envoyés ou acquités,</i></li>" +
                                                                         "<li><i>obligation pour les avions source et cible d’avoir pour compagnie AirCorsica,</i></li>" +
                                                                         "<li><i>obligation pour l’avion cible d’avoir des créneaux horaires libre pour ce(s) vol(s) déplacé(s) sur la période de la modification,</i></li>" +
                                                                         "<li><i>obligation pour l'avion cible s'il est de type différent d'avoir une durée de vol définie pour la(es) ligne(s) du(es) vol(s) à modfier sur la période de modification,</i></li></ul><br>";

                // récupération de la période de la saison courante
                // ------------------------------------------------
                var saveparam_periodesaisoncourante = new Array();
                saveparam_periodesaisoncourante['debut'] = $("#saisonselect1").find(":selected").attr('data-datedebutperiodesaison');
                saveparam_periodesaisoncourante['fin'] = $("#saisonselect1").find(":selected").attr('data-datefinperiodesaison');


                // récupération du nouvel avion attribué pour les vols selectionnés
                // ----------------------------------------------------------------
                var saveparam_idnouvelavionattribue = event.resourceId;


                // Gestion des différents cas de modifications
                // --------------------------------------------
                if (multiDropAllowedForSelection == true) {//on peut visuellement sur le jour courant faire la modification

                    if (typedemodificationplanning == 2) { //Type de modif: Période Custom ON (les modifications devront s'appliqué sur cette période custom)

                        var saveparam_ajoursemaine = new Array(); //les jours de la semaine selectionnés (0:Lundi / 1:Mardi ...)

                        if (typedeplanning == 0) { //Type de planning: Journalier ON (on travail sur un jour en particulier)

                            //on récupére le jour de la semaine (0:Lundi / 1:Mardi ...)
                            var curentmoment = $('#calendar').fullCalendar('getDate');
                            saveparam_ajoursemaine.push(eval(parseInt(curentmoment.format('d')) - 1));
                            /*
                            //rectification des date de la périodes en fonction du jour de la semaine courant
                            //par exemple si le jour courant est LUNDI, la date de debut sera un lundi et la date de fin également
                            var resultatPeriodeRectifie = findnewperiodestartendinfunctionofdaysofweekselected($("#periodecustomdatedebut").val(), $("#periodecustomdatefin").val(), saveparam_ajoursemaine);
                            var dateDeDebutPeriodeValide = resultatPeriodeRectifie['debut'];
                            var dateDeFinPeriodeValide = resultatPeriodeRectifie['fin'];
                            // A modifier ultérieurement partie php appelée car si la période est modifiée en fonction des/du jour (lundi, jeudi etc), comme elle n'est plus identique elle ne rentre pas dans le bon cas
                            */
                            var dateDeDebutPeriodeValide = $("#periodecustomdatedebut").val();
                            var dateDeFinPeriodeValide = $("#periodecustomdatefin").val();

                            // on fait confirmer la/les modifications par l'utilisateur
                            // --------------------------------------------------------
                            //on fait un reset de la gestion des evenement dans ce modal
                            $('#myMouseSelectionMoveModal').unbind();
                            //on définis le titre du modal
                            $('#myMouseSelectionMoveModalLabel').html('<i class="glyphicon glyphicon-plane fa-lg"></i> <strong>MODIFICATIONS JOURNALIERE/PERIODE A PARTIR D\'UNE SELECTION</strong>');
                            //on définis le texte du message
                            $('#myMouseSelectionMoveModalBody').html('<strong>Attention:</strong> êtes-vous sûr de vouloir changer <strong>' + listedescodesvoldelalert + '</strong><br>de: <strong>' + listedesavionsdelalert/*startresourceeventbeingdragged.title*/ + '</strong> vers <strong>' + newressource.title + '</strong><br>tous les: <strong>' + curentmoment.format('dddd') + '</strong><br>de la période: <strong>' + dateDeDebutPeriodeValide + '</strong> à <strong>' + dateDeFinPeriodeValide + '</strong>?' + warningambiguousavionsdelalert + warningrappelaverifierdelalert );
                            //on affiche le modal
                            $('#myMouseSelectionMoveModal').modal('show');
                            //lorsque le modal s'ouvre
                            $('#myMouseSelectionMoveModal').on('shown.bs.modal', function () {

                                // Affiche/Cache les prérequis de l'opération courante
                                $('#showrappelsaverifer').bind("click", function () {
                                    if($('#rappelsaverifier').hasClass('hide')){
                                        $('#rappelsaverifier').removeClass('hide');
                                    }else{
                                        $('#rappelsaverifier').addClass('hide')
                                    }
                                });

                                // OUI selectionné
                                $("#mouseSelectionMoveVALIDER").bind("click", function () {

                                    // pour utiliser les comparaison sur moment(.isSame(),.day(), ... ) il faut une date US
                                    var adateStrTmp = dateDeDebutPeriodeValide.split("-");
                                    var dateUSdebutPeriodeValide = adateStrTmp[2]+"-"+adateStrTmp[1]+"-"+adateStrTmp[0];
                                    var dateUSdebutMoment = new moment(dateUSdebutPeriodeValide);

                                    var adateStrTmpFin = dateDeFinPeriodeValide.split("-");
                                    var dateUSfinPeriodeValide = adateStrTmpFin[2]+"-"+adateStrTmpFin[1]+"-"+adateStrTmpFin[0];
                                    var dateUSfinMoment = new moment(dateUSfinPeriodeValide);

                                    var curentmoment = $('#calendar').fullCalendar('getDate');

                                     //période de 1 jour qui est le méme (mardi par ex) que celui du planning courant
                                     if ( ( dateUSdebutMoment.isSame(dateUSfinMoment) ) && ( eval(parseInt(dateUSdebutMoment.day())) == eval(parseInt(curentmoment.day())) ) ){// on utilise l'algo ponctuel

                                        var saveparam_ajours = new Array(); //les dates du jours selectionnés
                                        //on récupére le jour unique de la période d'un jour
                                        saveparam_ajours.push(dateDebutMoment.format('DD-MM-YYYY'));

                                        //url de routing
                                        var url = Routing.generate('planningvol_savemodificationavionvolponctuelle');

                                        //on affiche un SPinner de chargement
                                        $("#mySpinnerlabel").text("Modification(s) d'avion(s)...");
                                        $("#mySpinnerModal").modal("show");

                                        //On enregistre ces modification en BDD
                                        $.ajax({
                                            url: url,
                                            dataType: 'json',
                                            type: 'POST',
                                            data: {
                                                'arraydatesdelasemaine': saveparam_ajours,
                                                'arrayvolsid': selectedeventsarray,
                                                'ancienavionid': startresourceeventbeingdragged.id,
                                                'nouvelavionid': event.resourceId
                                            },
                                            success: function (doc) {

                                                //On enléve le spinner
                                                $("#mySpinnerModal").modal("hide");
                                                $("#mySpinnerlabel").text("Chargement jour courant...");

                                                var jsonobj = $.parseJSON(doc);

                                                if (jsonobj.success == false) {

                                                    // l'enregistrement des modifications dans la BDD a retournée une erreur, on affiche
                                                    bootbox.alert("<i class='glyphicon glyphicon-alert' style='color: red;'></i> " + jsonobj.erreur,
                                                        function () {
                                                            //on fais une raz
                                                            //on efface les selections de jours hebdomadaire
                                                            $('div[id^="PlHebd_"]').each(function () {
                                                                $(this).removeClass('joursem_sel');
                                                                $(this).children().first().next().remove();
                                                                selectedjoursplanninghebdomadairearray = new Array();
                                                            });

                                                            //on vide le tableau la modif viens d'étre effectuee
                                                            selectedeventsarray = [];

                                                            //on met a jour la var de session
                                                            var curentmoment = $('#calendar').fullCalendar('getDate');
                                                            majPlanningVolParameters(interactionsouris, selectedeventsarray, curentmoment.format('DD-MM-YYYY'), zoom_y, typedemodificationplanning, typedeplanning, periodecustomdebut, periodecustomfin, selectedIdSaison);
                                                            revertFunc(); //on annule le déplacement du vol "draggé"

                                                            //on recharge la page pour ré-initialiser le calendrier avec les infos valides à la fermeture de l'alerte
                                                            window.location.reload();
                                                        });

                                                } else {

                                                    //l'enregistrement c'est bien passé, on recharge la page pour ré-initialiser le calendrier avec les infos valides
                                                    window.location.reload();

                                                }

                                            },
                                            error: function () {//le serveur a retourné une erreur 500

                                                // l'enregistrement des modifications dans la BDD a retournée une erreur, on affiche
                                                bootbox.alert("<i class='glyphicon glyphicon-alert' style='color: red;'></i> Attention: l\'enregistrement des modifications n\'a put se terminer, le serveur a retourné une erreur 500!",
                                                    function () {

                                                        //on fais une raz
                                                        //on efface les selections de jours hebdomadaire
                                                        $('div[id^="PlHebd_"]').each(function () {
                                                            $(this).removeClass('joursem_sel');
                                                            $(this).children().first().next().remove();
                                                            selectedjoursplanninghebdomadairearray = new Array();
                                                        });

                                                        //on vide le tableau la modif viens d'étre effectuee
                                                        selectedeventsarray = [];

                                                        //on met a jour la var de session
                                                        var curentmoment = $('#calendar').fullCalendar('getDate');
                                                        majPlanningVolParameters(interactionsouris, selectedeventsarray, curentmoment.format('DD-MM-YYYY'), zoom_y, typedemodificationplanning, typedeplanning, periodecustomdebut, periodecustomfin, selectedIdSaison);
                                                        revertFunc(); //on annule le déplacement du vol "draggé"

                                                        //on recharge la page pour ré-initialiser le calendrier avec les infos valides à la fermeture de l'alerte
                                                        window.location.reload();
                                                    });

                                            }
                                        });

                                    } else {// on utilise l'algo période

                                         //PATCH JEANJO
                                         // alert('Cette fonctionalité est desactivée le correctif est en développement, elle sera ré-introduite à la prochaine mise en production.');
                                         // window.location.reload();
                                         //FIN PATCH

                                         //Code commenté en attendant que la partie PHP soit terminée
                                        var url = Routing.generate('planningvol_savemodificationavionvolsaisoncouranteoucustomperiode');

                                        //on affiche un SPinner de chargement
                                        $("#mySpinnerlabel").text("Modification(s) d'avion(s)...");
                                        $("#mySpinnerModal").modal("show");

                                        //On enregistre ces modification en BDD
                                        $.ajax({
                                           url: url,
                                           dataType: 'json',
                                           type: 'POST',
                                           data: {
                                               'periodesaisonid': $("#saisonselect1").find(":selected").val(),
                                               'periodesaisondebut': dateDeDebutPeriodeValide,
                                               'periodesaisonfin': dateDeFinPeriodeValide,
                                               'arrayjoursdelasemaine': saveparam_ajoursemaine,
                                               'arrayvolsid': selectedeventsarray,
                                               'ancienavionid': startresourceeventbeingdragged.id,
                                               'nouvelavionid': event.resourceId
                                           },
                                           success: function (doc) {

                                               //On enléve le spinner
                                               $("#mySpinnerModal").modal("hide");
                                               $("#mySpinnerlabel").text("Chargement jour courant...");

                                               var jsonobj = $.parseJSON(doc);

                                               if (jsonobj.success == false) {
                                                   // l'enregistrement des modifications dans la BDD a eu un pb,
                                               } else {
                                                   //l'enregistrement c'est bien passé
                                               }

                                               //on recharge la page pour ré-initialiser le calendrier avec les infos valides
                                               window.location.reload();

                                           },
                                           error: function () {//le serveur a retourné une erreur 500
                                               window.location.reload();
                                           }
                                        });

                                    }

                                    //on efface les selections de jours hebdomadaire
                                    $('div[id^="PlHebd_"]').each(function () {
                                        $(this).removeClass('joursem_sel');
                                        $(this).children().first().next().remove();
                                        selectedjoursplanninghebdomadairearray = new Array();
                                    });

                                    //on vide le tableau la modif viens d'étre effectuee
                                    selectedeventsarray = [];

                                    //on met a jour la var de session
                                    var curentmoment = $('#calendar').fullCalendar('getDate');
                                    majPlanningVolParameters(interactionsouris, selectedeventsarray, curentmoment.format('DD-MM-YYYY'), zoom_y, typedemodificationplanning, typedeplanning, periodecustomdebut, periodecustomfin,selectedIdSaison);

                                    //on ferme le modal
                                    $('#myMouseSelectionMoveModal').modal('hide');
                                });

                                // NON selectionné
                                $("#mouseSelectionMoveANNULER").bind("click", function () {

                                    //on efface les selections de jours hebdomadaire
                                    $('div[id^="PlHebd_"]').each(function () {
                                        $(this).removeClass('joursem_sel');
                                        $(this).children().first().next().remove();
                                        selectedjoursplanninghebdomadairearray = new Array();
                                    });

                                    //on vide le tableau la modif viens d'étre effectuee
                                    selectedeventsarray = [];

                                    //on met a jour la var de session
                                    var curentmoment = $('#calendar').fullCalendar('getDate');
                                    majPlanningVolParameters(interactionsouris, selectedeventsarray, curentmoment.format('DD-MM-YYYY'), zoom_y, typedemodificationplanning, typedeplanning, periodecustomdebut, periodecustomfin,selectedIdSaison);
                                    revertFunc(); //on annule le déplacement du vol "draggé"

                                    //on ferme le modal
                                    $('#myMouseSelectionMoveModal').modal('hide');
                                });

                            });

                        } else { //Type de planning: Hebdomadaire ON (on travail sur les jours de la semaine courante)

                            //on récupére les jours de la semaine selectionné dans le planning hebdomadaire (0:Lundi / 1:Mardi ...)
                            saveparam_ajoursemaine = new Array();
                            $('div[id^="PlHebd_"]').each(function () {

                                if ($(this).hasClass('joursem_sel')) {
                                    saveparam_ajoursemaine.push($(this).attr('data-weekday'));
                                }

                            });

                            /*
                            //rectification des date de la périodes en fonction des jours de la semaine selectionné
                            var resultatPeriodeRectifie = findnewperiodestartendinfunctionofdaysofweekselected($("#periodecustomdatedebut").val(), $("#periodecustomdatefin").val(), saveparam_ajoursemaine);
                            var dateDeDebutPeriodeValide = resultatPeriodeRectifie['debut'];
                            var dateDeFinPeriodeValide = resultatPeriodeRectifie['fin'];
                             // A modifier ultérieurement partie php appelée car si la période est modifiée en fonction des/du jour (lundi, jeudi etc), comme elle n'est plus identique elle ne rentre pas dans le bon cas
                            */
                            var dateDeDebutPeriodeValide = $("#periodecustomdatedebut").val();
                            var dateDeFinPeriodeValide = $("#periodecustomdatefin").val();

                            var listedesjourssemainedelalert = '';
                            saveparam_ajoursemaine.forEach(function (unnumerodejour) {
                                switch (parseInt(unnumerodejour)) {
                                    case 0:
                                        listedesjourssemainedelalert = listedesjourssemainedelalert + 'lundi,';
                                        break;
                                    case 1:
                                        listedesjourssemainedelalert = listedesjourssemainedelalert + 'mardi,';
                                        break;
                                    case 2:
                                        listedesjourssemainedelalert = listedesjourssemainedelalert + 'mercredi,';
                                        break;
                                    case 3:
                                        listedesjourssemainedelalert = listedesjourssemainedelalert + 'jeudi,';
                                        break;
                                    case 4:
                                        listedesjourssemainedelalert = listedesjourssemainedelalert + 'vendredi,';
                                        break;
                                    case 5:
                                        listedesjourssemainedelalert = listedesjourssemainedelalert + 'samedi,';
                                        break;
                                    case 6:
                                        listedesjourssemainedelalert = listedesjourssemainedelalert + 'dimanche,';
                                        break;
                                }
                            });
                            listedesjourssemainedelalert = listedesjourssemainedelalert.substr(0, listedesjourssemainedelalert.length - 1); //on enléve la derniére virgule et l'espace de fin

                            // on fait confirmer la/les modifications par l'utilisateur
                            // --------------------------------------------------------
                            //on fait un reset de la gestion des evenement dans ce modal
                            $('#myMouseSelectionMoveModal').unbind();
                            //on définis le titre du modal
                            $('#myMouseSelectionMoveModalLabel').html('<i class="glyphicon glyphicon-plane fa-lg"></i> <strong>MODIFICATIONS HEBDOMADAIRE/PERIODE A PARTIR D\'UNE SELECTION</strong>');
                            //on définis le texte du message
                            $('#myMouseSelectionMoveModalBody').html('<strong>Attention:</strong> êtes-vous sûr de vouloir changer: <strong>' + listedescodesvoldelalert + '</strong><br>de: <strong>' + listedesavionsdelalert/*startresourceeventbeingdragged.title*/ + '</strong> vers <strong>' + newressource.title + '</strong><br>tous les: <strong>' + listedesjourssemainedelalert + '</strong><br>de la période: <strong>' + dateDeDebutPeriodeValide + ' à ' + dateDeFinPeriodeValide + '</strong>?' + warningambiguousavionsdelalert + warningrappelaverifierdelalert );
                            //on affiche le modal
                            $('#myMouseSelectionMoveModal').modal('show');
                            //lorsque le modal s'ouvre
                            $('#myMouseSelectionMoveModal').on('shown.bs.modal', function () {

                                // Affiche/Cache les prérequis de l'opération courante
                                $('#showrappelsaverifer').bind("click", function () {
                                    if($('#rappelsaverifier').hasClass('hide')){
                                        $('#rappelsaverifier').removeClass('hide');
                                    }else{
                                        $('#rappelsaverifier').addClass('hide')

                                    }
                                });

                                // OUI selectionné
                                $("#mouseSelectionMoveVALIDER").bind("click", function () {

                                    // pour utiliser les comparaison sur moment(.isSame(),.day(), ... ) il faut une date US
                                    var adateStrTmp = dateDeDebutPeriodeValide.split("-");
                                    var dateUSdebutPeriodeValide = adateStrTmp[2]+"-"+adateStrTmp[1]+"-"+adateStrTmp[0];
                                    var dateUSdebutMoment = new moment(dateUSdebutPeriodeValide);

                                    var adateStrTmpFin = dateDeFinPeriodeValide.split("-");
                                    var dateUSfinPeriodeValide = adateStrTmpFin[2]+"-"+adateStrTmpFin[1]+"-"+adateStrTmpFin[0];
                                    var dateUSfinMoment = new moment(dateUSfinPeriodeValide);

                                    if ( ( dateUSdebutMoment.isSame(dateUSfinMoment) ) && ( saveparam_ajoursemaine.length == 1) && ( eval(parseInt(dateUSdebutMoment.day())) == eval(parseInt(saveparam_ajoursemaine[0])+1) ) ) {// on utilise l'algo ponctuel

                                        var saveparam_ajours = new Array(); //les dates du jours selectionnés
                                        //on récupére le jour unique de la période d'un jour
                                        saveparam_ajours.push(dateDebutMoment.format('DD-MM-YYYY'));

                                        //url de routing
                                        var url = Routing.generate('planningvol_savemodificationavionvolponctuelle');

                                        //on affiche un SPinner de chargement
                                        $("#mySpinnerlabel").text("Modification(s) d'avion(s)...");
                                        $("#mySpinnerModal").modal("show");

                                        //On enregistre ces modification en BDD
                                        $.ajax({
                                            url: url,
                                            dataType: 'json',
                                            type: 'POST',
                                            data: {
                                                'arraydatesdelasemaine': saveparam_ajours,
                                                'arrayvolsid': selectedeventsarray,
                                                'ancienavionid': startresourceeventbeingdragged.id,
                                                'nouvelavionid': event.resourceId
                                            },
                                            success: function (doc) {

                                                //On enléve le spinner
                                                $("#mySpinnerModal").modal("hide");
                                                $("#mySpinnerlabel").text("Chargement jour courant...");

                                                var jsonobj = $.parseJSON(doc);

                                                if (jsonobj.success == false) {

                                                    // l'enregistrement des modifications dans la BDD a retournée une erreur, on affiche
                                                    bootbox.alert("<i class='glyphicon glyphicon-alert' style='color: red;'></i> " + jsonobj.erreur,
                                                        function () {
                                                            //on fais une raz
                                                            //on efface les selections de jours hebdomadaire
                                                            $('div[id^="PlHebd_"]').each(function () {
                                                                $(this).removeClass('joursem_sel');
                                                                $(this).children().first().next().remove();
                                                                selectedjoursplanninghebdomadairearray = new Array();
                                                            });

                                                            //on vide le tableau la modif viens d'étre effectuee
                                                            selectedeventsarray = [];

                                                            //on met a jour la var de session
                                                            var curentmoment = $('#calendar').fullCalendar('getDate');
                                                            majPlanningVolParameters(interactionsouris, selectedeventsarray, curentmoment.format('DD-MM-YYYY'), zoom_y, typedemodificationplanning, typedeplanning, periodecustomdebut, periodecustomfin, selectedIdSaison);
                                                            revertFunc(); //on annule le déplacement du vol "draggé"

                                                            //on recharge la page pour ré-initialiser le calendrier avec les infos valides à la fermeture de l'alerte
                                                            window.location.reload();
                                                        });

                                                } else {

                                                    //l'enregistrement c'est bien passé, on recharge la page pour ré-initialiser le calendrier avec les infos valides
                                                    window.location.reload();

                                                }

                                            },
                                            error: function () {//le serveur a retourné une erreur 500

                                                // l'enregistrement des modifications dans la BDD a retournée une erreur, on affiche
                                                bootbox.alert("<i class='glyphicon glyphicon-alert' style='color: red;'></i> Attention: l\'enregistrement des modifications n\'a put se terminer, le serveur a retourné une erreur 500!",
                                                    function () {

                                                        //on fais une raz
                                                        //on efface les selections de jours hebdomadaire
                                                        $('div[id^="PlHebd_"]').each(function () {
                                                            $(this).removeClass('joursem_sel');
                                                            $(this).children().first().next().remove();
                                                            selectedjoursplanninghebdomadairearray = new Array();
                                                        });

                                                        //on vide le tableau la modif viens d'étre effectuee
                                                        selectedeventsarray = [];

                                                        //on met a jour la var de session
                                                        var curentmoment = $('#calendar').fullCalendar('getDate');
                                                        majPlanningVolParameters(interactionsouris, selectedeventsarray, curentmoment.format('DD-MM-YYYY'), zoom_y, typedemodificationplanning, typedeplanning, periodecustomdebut, periodecustomfin, selectedIdSaison);
                                                        revertFunc(); //on annule le déplacement du vol "draggé"

                                                        //on recharge la page pour ré-initialiser le calendrier avec les infos valides à la fermeture de l'alerte
                                                        window.location.reload();
                                                    });

                                            }
                                        });






                                    }else {//algo periode

                                        //PATCH JEANJO
                                        // alert('Cette fonctionalité est desactivée le correctif est en développement, elle sera ré-introduite à la prochaine mise en production.');
                                        // window.location.reload();
                                        //FIN PATCH

                                        var url = Routing.generate('planningvol_savemodificationavionvolsaisoncouranteoucustomperiode');

                                        //on affiche un SPinner de chargement
                                        $("#mySpinnerlabel").text("Modification(s) d'avion(s)...");
                                        $("#mySpinnerModal").modal("show");

                                        //On enregistre ces modification en BDD
                                        $.ajax({
                                           url: url,
                                           dataType: 'json',
                                           type: 'POST',
                                           data: {
                                               'periodesaisonid': $("#saisonselect1").find(":selected").val(),
                                               'periodesaisondebut': dateDeDebutPeriodeValide,
                                               'periodesaisonfin': dateDeFinPeriodeValide,
                                               'arrayjoursdelasemaine': saveparam_ajoursemaine,
                                               'arrayvolsid': selectedeventsarray,
                                               'ancienavionid': startresourceeventbeingdragged.id,
                                               'nouvelavionid': event.resourceId
                                           },
                                           success: function (doc) {

                                               //On enléve le spinner
                                               $("#mySpinnerModal").modal("hide");
                                               $("#mySpinnerlabel").text("Chargement jour courant...");

                                               var jsonobj = $.parseJSON(doc);

                                               if (jsonobj.success == false) {
                                                   // l'enregistrement des modifications dans la BDD a eu un pb,
                                               } else {
                                                   //l'enregistrement c'est bien passé
                                               }

                                               //on recharge la page pour ré-initialiser le calendrier avec les infos valides
                                               window.location.reload();

                                           },
                                           error: function () {//le serveur a retourné une erreur 500
                                               window.location.reload();
                                           }
                                        });

                                    }

                                    //on efface les selections de jours hebdomadaire
                                    $('div[id^="PlHebd_"]').each(function () {
                                        $(this).removeClass('joursem_sel');
                                        $(this).children().first().next().remove();
                                        selectedjoursplanninghebdomadairearray = new Array();
                                    });

                                    //on vide le tableau la modif viens d'étre effectuee
                                    selectedeventsarray = [];

                                    //on met a jour la var de session
                                    var curentmoment = $('#calendar').fullCalendar('getDate');
                                    majPlanningVolParameters(interactionsouris, selectedeventsarray, curentmoment.format('DD-MM-YYYY'), zoom_y, typedemodificationplanning, typedeplanning, periodecustomdebut, periodecustomfin,selectedIdSaison);

                                    //on ferme le modal
                                    $('#myMouseSelectionMoveModal').modal('hide');
                                });

                                // NON selectionné
                                $("#mouseSelectionMoveANNULER").bind("click", function () {

                                    //on efface les selections de jours hebdomadaire
                                    $('div[id^="PlHebd_"]').each(function () {
                                        $(this).removeClass('joursem_sel');
                                        $(this).children().first().next().remove();
                                        selectedjoursplanninghebdomadairearray = new Array();
                                    });

                                    //on vide le tableau la modif viens d'étre effectuee
                                    selectedeventsarray = [];

                                    //on met a jour la var de session
                                    var curentmoment = $('#calendar').fullCalendar('getDate');
                                    majPlanningVolParameters(interactionsouris, selectedeventsarray, curentmoment.format('DD-MM-YYYY'), zoom_y, typedemodificationplanning, typedeplanning, periodecustomdebut, periodecustomfin,selectedIdSaison);
                                    revertFunc(); //on annule le déplacement du vol "draggé"

                                    //on ferme le modal
                                    $('#myMouseSelectionMoveModal').modal('hide');
                                });

                            });

                        }

                    } else if (typedemodificationplanning == 1) { //Type de modif: Saison Courante ON (les modifications devront s'appliqué sur cette période)


                        var saveparam_ajoursemaine = new Array(); //les jours de la semaine selectionnés (0:Lundi / 1:Mardi ...)

                        if (typedeplanning == 0) { //Type de planning: Journalier ON (on travail sur un jour en particulier)

                            //on récupére le jour de la semaine (0:Lundi / 1:Mardi ...)
                            var curentmoment = $('#calendar').fullCalendar('getDate');
                            saveparam_ajoursemaine.push(eval(parseInt(curentmoment.format('d')) - 1));

                            /*
                            //rectification des date de la périodes en fonction des jours de la semaine selectionné
                            var resultatPeriodeRectifie = findnewperiodestartendinfunctionofdaysofweekselected($("#saisonselect1").find(":selected").attr('data-datedebutperiodesaison').split(' ')[0], $("#saisonselect1").find(":selected").attr('data-datefinperiodesaison').split(' ')[0], saveparam_ajoursemaine);
                            var dateDeDebutPeriodeValide = resultatPeriodeRectifie['debut'];
                            var dateDeFinPeriodeValide = resultatPeriodeRectifie['fin'];
                             // A modifier ultérieurement partie php appelée car si la période est modifiée en fonction des/du jour (lundi, jeudi etc), comme elle n'est plus identique elle ne rentre pas dans le bon cas
                            */
                            var nomsaisonValide = $("#saisonselect1").find(":selected").text();
                            var dateDeDebutPeriodeValide = $("#saisonselect1").find(":selected").attr('data-datedebutperiodesaison').split(' ')[0];
                            var dateDebutMoment = new moment(dateDeDebutPeriodeValide);
                            var dateDeFinPeriodeValide = $("#saisonselect1").find(":selected").attr('data-datefinperiodesaison').split(' ')[0];
                            var dateFinMoment = new moment(dateDeFinPeriodeValide);

                            // on fait confirmer la/les modifications par l'utilisateur
                            // --------------------------------------------------------
                            //on fait un reset de la gestion des evenement dans ce modal
                            $('#myMouseSelectionMoveModal').unbind();
                            //on définis le titre du modal
                            $('#myMouseSelectionMoveModalLabel').html('<i class="glyphicon glyphicon-plane fa-lg"></i> <strong>MODIFICATIONS JOURNALIERE/SAISON A PARTIR D\'UNE SELECTION</strong>');
                            //on définis le texte du message
                            $('#myMouseSelectionMoveModalBody').html('<strong>Attention:</strong> êtes-vous sûr de vouloir changer: <strong>' + listedescodesvoldelalert + '</strong><br>de:<strong>' + listedesavionsdelalert/*startresourceeventbeingdragged.title*/ + '</strong> vers <strong>' + newressource.title + '</strong><br>tous les: <strong>' + curentmoment.format('dddd') + '</strong><br>de la saison: <strong>'+ nomsaisonValide + '</strong> définie du <strong>' + dateDebutMoment.format('DD-MM-YYYY') + '</strong> au <strong>' + dateFinMoment.format('DD-MM-YYYY') + '</strong>?' + warningambiguousavionsdelalert + warningrappelaverifierdelalert );
                            //on affiche le modal
                            $('#myMouseSelectionMoveModal').modal('show');
                            //lorsque le modal s'ouvre
                            $('#myMouseSelectionMoveModal').on('shown.bs.modal', function () {

                                // Affiche/Cache les prérequis de l'opération courante
                                $('#showrappelsaverifer').bind("click", function () {
                                    if($('#rappelsaverifier').hasClass('hide')){
                                        $('#rappelsaverifier').removeClass('hide');
                                    }else{
                                        $('#rappelsaverifier').addClass('hide')

                                    }
                                });

                                // OUI selectionné
                                $("#mouseSelectionMoveVALIDER").bind("click", function () {

                                    //PATCH JEANJO
                                    // alert('Cette fonctionalité est desactivée le correctif est en développement, elle sera ré-introduite à la prochaine mise en production.');
                                    // window.location.reload();
                                    //FIN PATCH

                                    var url = Routing.generate('planningvol_savemodificationavionvolsaisoncouranteoucustomperiode');

                                    //on affiche un SPinner de chargement
                                    $("#mySpinnerlabel").text("Modification(s) d'avion(s)...");
                                    $("#mySpinnerModal").modal("show");

                                    //On enregistre ces modification en BDD
                                    $.ajax({
                                       url: url,
                                       dataType: 'json',
                                       type: 'POST',
                                       data: {
                                           'periodesaisonid': $("#saisonselect1").find(":selected").val(),
                                           'periodesaisondebut': dateDeDebutPeriodeValide,
                                           'periodesaisonfin': dateDeFinPeriodeValide,
                                           'arrayjoursdelasemaine': saveparam_ajoursemaine,
                                           'arrayvolsid': selectedeventsarray,
                                           'ancienavionid': startresourceeventbeingdragged.id,
                                           'nouvelavionid': event.resourceId
                                       },
                                       success: function (doc) {

                                           //On enléve le spinner
                                           $("#mySpinnerModal").modal("hide");
                                           $("#mySpinnerlabel").text("Chargement jour courant...");

                                           var jsonobj = $.parseJSON(doc);

                                           if (jsonobj.success == false) {
                                               // l'enregistrement des modifications dans la BDD a eu un pb,
                                           } else {
                                               //l'enregistrement c'est bien passé
                                           }

                                           //on recharge la page pour ré-initialiser le calendrier avec les infos valides
                                           window.location.reload();

                                       },
                                       error: function () {//le serveur a retourné une erreur 500
                                           window.location.reload();
                                       }
                                    });

                                    //on efface les selections de jours hebdomadaire
                                    $('div[id^="PlHebd_"]').each(function () {
                                        $(this).removeClass('joursem_sel');
                                        $(this).children().first().next().remove();
                                        selectedjoursplanninghebdomadairearray = new Array();
                                    });

                                    //on vide le tableau la modif viens d'étre effectuee
                                    selectedeventsarray = [];

                                    //on met a jour la var de session
                                    var curentmoment = $('#calendar').fullCalendar('getDate');
                                    majPlanningVolParameters(interactionsouris, selectedeventsarray, curentmoment.format('DD-MM-YYYY'), zoom_y, typedemodificationplanning, typedeplanning, periodecustomdebut, periodecustomfin,selectedIdSaison);

                                    //on ferme le modal
                                    $('#myMouseSelectionMoveModal').modal('hide');
                                });

                                // NON selectionné
                                $("#mouseSelectionMoveANNULER").bind("click", function () {

                                    //on efface les selections de jours hebdomadaire
                                    $('div[id^="PlHebd_"]').each(function () {
                                        $(this).removeClass('joursem_sel');
                                        $(this).children().first().next().remove();
                                        selectedjoursplanninghebdomadairearray = new Array();
                                    });

                                    //on vide le tableau la modif viens d'étre effectuee
                                    selectedeventsarray = [];

                                    //on met a jour la var de session
                                    var curentmoment = $('#calendar').fullCalendar('getDate');
                                    majPlanningVolParameters(interactionsouris, selectedeventsarray, curentmoment.format('DD-MM-YYYY'), zoom_y, typedemodificationplanning, typedeplanning, periodecustomdebut, periodecustomfin,selectedIdSaison);
                                    revertFunc(); //on annule le déplacement du vol "draggé"

                                    //on ferme le modal
                                    $('#myMouseSelectionMoveModal').modal('hide');
                                });

                            });

                        } else { //Type de planning: Hebdomadaire ON (on travail sur les jours de la semaine courante)

                            //on récupére les jours de la semaine selectionné dans le planning hebdomadaire (0:Lundi / 1:Mardi ...)
                            saveparam_ajoursemaine = new Array();
                            $('div[id^="PlHebd_"]').each(function () {

                                if ($(this).hasClass('joursem_sel')) {
                                    saveparam_ajoursemaine.push($(this).attr('data-weekday'));
                                }

                            });

                            /*
                            //rectification des date de la périodes en fonction des jours de la semaine selectionné
                            var resultatPeriodeRectifie = findnewperiodestartendinfunctionofdaysofweekselected($("#saisonselect1").find(":selected").attr('data-datedebutperiodesaison').split(' ')[0], $("#saisonselect1").find(":selected").attr('data-datefinperiodesaison').split(' ')[0], saveparam_ajoursemaine);
                            var dateDeDebutPeriodeValide = resultatPeriodeRectifie['debut'];
                            var dateDeFinPeriodeValide = resultatPeriodeRectifie['fin'];
                             // A modifier ultérieurement partie php appelée car si la période est modifiée en fonction des/du jour (lundi, jeudi etc), comme elle n'est plus identique elle ne rentre pas dans le bon cas
                            */

                            var nomsaisonValide = $("#saisonselect1").find(":selected").text();
                            var dateDeDebutPeriodeValide = $("#saisonselect1").find(":selected").attr('data-datedebutperiodesaison').split(' ')[0];
                            var dateDebutMoment = new moment(dateDeDebutPeriodeValide);
                            var dateDeFinPeriodeValide = $("#saisonselect1").find(":selected").attr('data-datefinperiodesaison').split(' ')[0];
                            var dateFinMoment = new moment(dateDeFinPeriodeValide);


                            var listedesjourssemainedelalert = '';
                            saveparam_ajoursemaine.forEach(function (unnumerodejour) {
                                switch (parseInt(unnumerodejour)) {
                                    case 0:
                                        listedesjourssemainedelalert = listedesjourssemainedelalert + 'lundi,';
                                        break;
                                    case 1:
                                        listedesjourssemainedelalert = listedesjourssemainedelalert + 'mardi,';
                                        break;
                                    case 2:
                                        listedesjourssemainedelalert = listedesjourssemainedelalert + 'mercredi,';
                                        break;
                                    case 3:
                                        listedesjourssemainedelalert = listedesjourssemainedelalert + 'jeudi,';
                                        break;
                                    case 4:
                                        listedesjourssemainedelalert = listedesjourssemainedelalert + 'vendredi,';
                                        break;
                                    case 5:
                                        listedesjourssemainedelalert = listedesjourssemainedelalert + 'samedi,';
                                        break;
                                    case 6:
                                        listedesjourssemainedelalert = listedesjourssemainedelalert + 'dimanche,';
                                        break;
                                }
                            });
                            listedesjourssemainedelalert = listedesjourssemainedelalert.substr(0, listedesjourssemainedelalert.length - 1); //on enléve la derniére virgule et l'espace de fin


                            // on fait confirmer la/les modifications par l'utilisateur
                            // --------------------------------------------------------
                            //on fait un reset de la gestion des evenement dans ce modal
                            $('#myMouseSelectionMoveModal').unbind();
                            //on définis le titre du modal
                            $('#myMouseSelectionMoveModalLabel').html('<i class="glyphicon glyphicon-plane fa-lg"></i> <strong>MODIFICATIONS HEBDOMADAIRE/SAISON A PARTIR D\'UNE SELECTION</strong>');
                            //on définis le texte du message
                            $('#myMouseSelectionMoveModalBody').html('<strong>Attention:</strong> êtes-vous sûr de vouloir changer: <strong>' + listedescodesvoldelalert + '</strong><br>de: <strong>' + listedesavionsdelalert/*startresourceeventbeingdragged.title*/ + '</strong> vers <strong>' + newressource.title + '</strong><br>tous les: <strong>' + listedesjourssemainedelalert + '</strong><br>de la saison: <strong>'+ nomsaisonValide + '</strong> définie du <strong>' + dateDebutMoment.format('DD-MM-YYYY') + '</strong> au <strong>' + dateFinMoment.format('DD-MM-YYYY') + '</strong>?' + warningambiguousavionsdelalert + warningrappelaverifierdelalert );
                            //on affiche le modal
                            $('#myMouseSelectionMoveModal').modal('show');
                            //lorsque le modal s'ouvre
                            $('#myMouseSelectionMoveModal').on('shown.bs.modal', function () {

                                // Affiche/Cache les prérequis de l'opération courante
                                $('#showrappelsaverifer').bind("click", function () {
                                    if($('#rappelsaverifier').hasClass('hide')){
                                        $('#rappelsaverifier').removeClass('hide');
                                    }else{
                                        $('#rappelsaverifier').addClass('hide')

                                    }
                                });

                                // OUI selectionné
                                $("#mouseSelectionMoveVALIDER").bind("click", function () {

                                    //PATCH JEANJO
                                    // alert('Cette fonctionalité est desactivée le correctif est en développement, elle sera ré-introduite à la prochaine mise en production.');
                                    // window.location.reload();
                                    //FIN PATCH

                                    var url = Routing.generate('planningvol_savemodificationavionvolsaisoncouranteoucustomperiode');

                                    //on affiche un SPinner de chargement
                                    $("#mySpinnerlabel").text("Modification(s) d'avion(s)...");
                                    $("#mySpinnerModal").modal("show");

                                    //On enregistre ces modification en BDD
                                    $.ajax({
                                       url: url,
                                       dataType: 'json',
                                       type: 'POST',
                                       data: {
                                           'periodesaisonid': $("#saisonselect1").find(":selected").val(),
                                           'periodesaisondebut': dateDeDebutPeriodeValide,
                                           'periodesaisonfin': dateDeFinPeriodeValide,
                                           'arrayjoursdelasemaine': saveparam_ajoursemaine,
                                           'arrayvolsid': selectedeventsarray,
                                           'ancienavionid': startresourceeventbeingdragged.id,
                                           'nouvelavionid': event.resourceId
                                       },
                                       success: function (doc) {

                                           //On enléve le spinner
                                           $("#mySpinnerModal").modal("hide");
                                           $("#mySpinnerlabel").text("Chargement jour courant...");

                                           var jsonobj = $.parseJSON(doc);

                                           if (jsonobj.success == false) {
                                               // l'enregistrement des modifications dans la BDD a eu un pb,
                                           } else {
                                               //l'enregistrement c'est bien passé
                                           }

                                           //on recharge la page pour ré-initialiser le calendrier avec les infos valides
                                           window.location.reload();

                                       },
                                       error: function () {//le serveur a retourné une erreur 500
                                           window.location.reload();
                                       }
                                    });

                                    //on efface les selections de jours hebdomadaire
                                    $('div[id^="PlHebd_"]').each(function () {
                                        $(this).removeClass('joursem_sel');
                                        $(this).children().first().next().remove();
                                        selectedjoursplanninghebdomadairearray = new Array();
                                    });

                                    //on vide le tableau la modif viens d'étre effectuee
                                    selectedeventsarray = [];

                                    //on met a jour la var de session
                                    var curentmoment = $('#calendar').fullCalendar('getDate');
                                    majPlanningVolParameters(interactionsouris, selectedeventsarray, curentmoment.format('DD-MM-YYYY'), zoom_y, typedemodificationplanning, typedeplanning, periodecustomdebut, periodecustomfin,selectedIdSaison);

                                    //on ferme le modal
                                    $('#myMouseSelectionMoveModal').modal('hide');
                                });

                                // NON selectionné
                                $("#mouseSelectionMoveANNULER").bind("click", function () {

                                    //on efface les selections de jours hebdomadaire
                                    $('div[id^="PlHebd_"]').each(function () {
                                        $(this).removeClass('joursem_sel');
                                        $(this).children().first().next().remove();
                                        selectedjoursplanninghebdomadairearray = new Array();
                                    });

                                    //on vide le tableau la modif viens d'étre effectuee
                                    selectedeventsarray = [];

                                    //on met a jour la var de session
                                    var curentmoment = $('#calendar').fullCalendar('getDate');
                                    majPlanningVolParameters(interactionsouris, selectedeventsarray, curentmoment.format('DD-MM-YYYY'), zoom_y, typedemodificationplanning, typedeplanning, periodecustomdebut, periodecustomfin,selectedIdSaison);
                                    revertFunc(); //on annule le déplacement du vol "draggé"

                                    //on ferme le modal
                                    $('#myMouseSelectionMoveModal').modal('hide');
                                });

                            });

                        }

                    } else { //Type de modif: Ponctuelle ON (les modifications devront s'appliqué sur le jour ou les jours hebdomadaire selectionné)

                        var saveparam_ajours = new Array(); //les dates du jours selectionnés

                        if (typedeplanning == 0) { //Type de planning: Journalier ON (on travail sur un jour en particulier)

                            //on récupére le jour courant
                            var curentmoment = $('#calendar').fullCalendar('getDate');
                            saveparam_ajours.push(curentmoment.format('DD-MM-YYYY'));

                            // on fait confirmer la/les modifications par l'utilisateur
                            // --------------------------------------------------------

                            //on fait un reset de la gestion des evenement dans ce modal
                            $('#myMouseSelectionMoveModal').unbind();
                            //on définis le titre du modal
                            $('#myMouseSelectionMoveModalLabel').html('<i class="glyphicon glyphicon-plane fa-lg"></i> <strong>MODIFICATIONS JOURNALIERE/PONCTUELLE A PARTIR D\'UNE SELECTION</strong>');
                            //on définis le texte du message
                            $('#myMouseSelectionMoveModalBody').html('<strong>Attention:</strong> êtes-vous sûr de vouloir changer: <strong>' + listedescodesvoldelalert + '</strong><br>de: <strong>' + listedesavionsdelalert/*startresourceeventbeingdragged.title*/ + '</strong> vers <strong>' + newressource.title + '</strong><br>le <strong>' + curentmoment.format('dddd') + ' ' + saveparam_ajours[0] + '</strong>?' + warningambiguousavionsdelalert + warningrappelaverifierdelalert );
                            //on affiche le modal
                            $('#myMouseSelectionMoveModal').modal('show');
                            //lorsque le modal s'ouvre
                            $('#myMouseSelectionMoveModal').on('shown.bs.modal', function () {

                                // Affiche/Cache les prérequis de l'opération courante
                                $('#showrappelsaverifer').bind("click", function () {
                                    if($('#rappelsaverifier').hasClass('hide')){
                                        $('#rappelsaverifier').removeClass('hide');
                                    }else{
                                        $('#rappelsaverifier').addClass('hide')

                                    }
                                });

                                // OUI selectionné
                                $("#mouseSelectionMoveVALIDER").bind("click", function () {

                                    var url = Routing.generate('planningvol_savemodificationavionvolponctuelle');

                                    //on affiche un SPinner de chargement
                                    $("#mySpinnerlabel").text("Modification(s) d'avion(s)...");
                                    $("#mySpinnerModal").modal("show");

                                    //On enregistre ces modification en BDD
                                    $.ajax({
                                        url: url,
                                        dataType: 'json',
                                        type: 'POST',
                                        data: {
                                            'arraydatesdelasemaine': saveparam_ajours,
                                            'arrayvolsid': selectedeventsarray,
                                            'ancienavionid': startresourceeventbeingdragged.id,
                                            'nouvelavionid': event.resourceId,
                                            'appelMenuContextuel': false
                                        },
                                        success: function (doc) {

                                            //On enléve le spinner
                                            $("#mySpinnerModal").modal("hide");
                                            $("#mySpinnerlabel").text("Chargement jour courant...");

                                            var jsonobj = $.parseJSON(doc);

                                            if (jsonobj.success == false) {
                                                // l'enregistrement des modifications dans la BDD a eu un pb,
                                            } else {
                                                //l'enregistrement c'est bien passé
                                            }

                                            //on recharge la page pour ré-initialiser le calendrier avec les infos valides
                                            window.location.reload();

                                        },
                                        error: function () {//le serveur a retourné une erreur 500

                                            // l'enregistrement des modifications dans la BDD a retournée une erreur, on affiche
                                            bootbox.alert("<i class='glyphicon glyphicon-alert' style='color: red;'></i> Attention: l\'enregistrement des modifications n\'a put se terminer, le serveur a retourné une erreur 500!",
                                                function (){

                                                    //on fais une raz
                                                    //on efface les selections de jours hebdomadaire
                                                    $('div[id^="PlHebd_"]').each(function () {
                                                        $(this).removeClass('joursem_sel');
                                                        $(this).children().first().next().remove();
                                                        selectedjoursplanninghebdomadairearray = new Array();
                                                    });

                                                    //on vide le tableau la modif viens d'étre effectuee
                                                    selectedeventsarray = [];

                                                    //on met a jour la var de session
                                                    var curentmoment = $('#calendar').fullCalendar('getDate');
                                                    majPlanningVolParameters(interactionsouris, selectedeventsarray, curentmoment.format('DD-MM-YYYY'), zoom_y, typedemodificationplanning, typedeplanning, periodecustomdebut, periodecustomfin,selectedIdSaison);
                                                    revertFunc(); //on annule le déplacement du vol "draggé"

                                                    //on recharge la page pour ré-initialiser le calendrier avec les infos valides à la fermeture de l'alerte
                                                    window.location.reload();
                                                });

                                        }
                                    });

                                    //on efface les selections de jours hebdomadaire
                                    $('div[id^="PlHebd_"]').each(function () {
                                        $(this).removeClass('joursem_sel');
                                        $(this).children().first().next().remove();
                                        selectedjoursplanninghebdomadairearray = new Array();
                                    });

                                    //on vide le tableau la modif viens d'étre effectuee
                                    selectedeventsarray = [];

                                    //on met a jour la var de session
                                    var curentmoment = $('#calendar').fullCalendar('getDate');
                                    majPlanningVolParameters(interactionsouris, selectedeventsarray, curentmoment.format('DD-MM-YYYY'), zoom_y, typedemodificationplanning, typedeplanning, periodecustomdebut, periodecustomfin,selectedIdSaison);

                                    //on ferme le modal
                                    $('#myMouseSelectionMoveModal').modal('hide');
                                });

                                // NON selectionné
                                $("#mouseSelectionMoveANNULER").bind("click", function () {

                                    //on efface les selections de jours hebdomadaire
                                    $('div[id^="PlHebd_"]').each(function () {
                                        $(this).removeClass('joursem_sel');
                                        $(this).children().first().next().remove();
                                        selectedjoursplanninghebdomadairearray = new Array();
                                    });

                                    //on vide le tableau la modif viens d'étre effectuee
                                    selectedeventsarray = [];

                                    //on met a jour la var de session
                                    var curentmoment = $('#calendar').fullCalendar('getDate');
                                    majPlanningVolParameters(interactionsouris, selectedeventsarray, curentmoment.format('DD-MM-YYYY'), zoom_y, typedemodificationplanning, typedeplanning, periodecustomdebut, periodecustomfin,selectedIdSaison);
                                    revertFunc(); //on annule le déplacement du vol "draggé"

                                    //on ferme le modal
                                    $('#myMouseSelectionMoveModal').modal('hide');
                                });

                            });

                        } else { //Type de planning: Hebdomadaire ON (on travail sur les jours de la semaine courante)

                            //on récupére les jours de la semaine selectionné dans le planning hebdomadaire (0:Lundi / 1:Mardi ...)
                            saveparam_ajoursemaine = new Array();
                            $('div[id^="PlHebd_"]').each(function () {

                                if ($(this).hasClass('joursem_sel')) {
                                    saveparam_ajoursemaine.push($(this).attr('data-weekday'));
                                }

                            });

                            var listedesjourssemainedelalert = '';
                            saveparam_ajoursemaine.forEach(function (unnumerodejour) {
                                switch (parseInt(unnumerodejour)) {
                                    case 0:
                                        listedesjourssemainedelalert = listedesjourssemainedelalert + 'lundi,';
                                        break;
                                    case 1:
                                        listedesjourssemainedelalert = listedesjourssemainedelalert + 'mardi,';
                                        break;
                                    case 2:
                                        listedesjourssemainedelalert = listedesjourssemainedelalert + 'mercredi,';
                                        break;
                                    case 3:
                                        listedesjourssemainedelalert = listedesjourssemainedelalert + 'jeudi,';
                                        break;
                                    case 4:
                                        listedesjourssemainedelalert = listedesjourssemainedelalert + 'vendredi,';
                                        break;
                                    case 5:
                                        listedesjourssemainedelalert = listedesjourssemainedelalert + 'samedi,';
                                        break;
                                    case 6:
                                        listedesjourssemainedelalert = listedesjourssemainedelalert + 'dimanche,';
                                        break;
                                }
                            });
                            listedesjourssemainedelalert = listedesjourssemainedelalert.substr(0, listedesjourssemainedelalert.length - 1); //on enléve la derniére virgule et l'espace de fin

                            //calcul de la date debut et la date fin
                            var updatemoment = $('#calendar').fullCalendar('getDate');
                            //la date de début période sera le début de la semaine courante
                            dateDeDebutPeriodeValide = updatemoment.startOf('isoWeek').format('DD-MM-YYYY');

                            //la date de fin période sera la fin de la semaine courante
                            dateDeFinPeriodeValide = updatemoment.endOf('isoWeek').format('DD-MM-YYYY');

                            // on fait confirmer la/les modifications par l'utilisateur
                            // --------------------------------------------------------
                            //on fait un reset de la gestion des evenement dans ce modal
                            $('#myMouseSelectionMoveModal').unbind();
                            //on définis le titre du modal
                            $('#myMouseSelectionMoveModalLabel').html('<i class="glyphicon glyphicon-plane fa-lg"></i> <strong>MODIFICATIONS HEBDOMADAIRE/PONCTUELLE A PARTIR D\'UNE SELECTION</strong>');
                            //on définis le texte du message
                            $('#myMouseSelectionMoveModalBody').html('<strong>Attention:</strong> êtes-vous sûr de vouloir changer: <strong>' + listedescodesvoldelalert + '</strong><br>de: <strong>' + listedesavionsdelalert/*startresourceeventbeingdragged.title*/ + '</strong> vers <strong>' + newressource.title + '</strong><br>les: <strong>' + listedesjourssemainedelalert + '</strong><br>de la semaine: <strong>' + dateDeDebutPeriodeValide + ' à ' + dateDeFinPeriodeValide + '</strong>?' + warningambiguousavionsdelalert + warningrappelaverifierdelalert );
                            //on affiche le modal
                            $('#myMouseSelectionMoveModal').modal('show');

                            //lorsque le modal s'ouvre
                            $('#myMouseSelectionMoveModal').on('shown.bs.modal', function () {

                                // Affiche/Cache les prérequis de l'opération courante
                                $('#showrappelsaverifer').bind("click", function () {
                                    if($('#rappelsaverifier').hasClass('hide')){
                                        $('#rappelsaverifier').removeClass('hide');
                                    }else{
                                        $('#rappelsaverifier').addClass('hide')

                                    }
                                });

                                // OUI selectionné
                                $("#mouseSelectionMoveVALIDER").bind("click", function () {

                                    if (saveparam_ajoursemaine.length > 1) {// on utilise l'algo période

                                        //PATCH JEANJO
                                        // alert('Cette fonctionalité est desactivée le correctif est en développement, elle sera ré-introduite à la prochaine mise en production.');
                                        // window.location.reload();
                                        //FIN PATCH

                                        //url de routing
                                        var url = Routing.generate('planningvol_savemodificationavionvolsaisoncouranteoucustomperiode');

                                        //on affiche un SPinner de chargement
                                        $("#mySpinnerlabel").text("Modification(s) d'avion(s)...");
                                        $("#mySpinnerModal").modal("show");

                                        //On enregistre ces modification en BDD
                                        $.ajax({
                                           url: url,
                                           dataType: 'json',
                                           type: 'POST',
                                           data: {
                                               'periodesaisonid': $("#saisonselect1").find(":selected").val(),
                                               'periodesaisondebut': dateDeDebutPeriodeValide, //ok
                                               'periodesaisonfin': dateDeFinPeriodeValide, //ok
                                               'arrayjoursdelasemaine': saveparam_ajoursemaine,
                                               'arrayvolsid': selectedeventsarray,
                                               'ancienavionid': startresourceeventbeingdragged.id,
                                               'nouvelavionid': event.resourceId
                                           },
                                           success: function (doc) {

                                               //On enléve le spinner
                                               $("#mySpinnerModal").modal("hide");
                                               $("#mySpinnerlabel").text("Chargement jour courant...");

                                               var jsonobj = $.parseJSON(doc);

                                               if (jsonobj.success == false) {
                                                   // l'enregistrement des modifications dans la BDD a eu un pb,
                                               } else {
                                                   //l'enregistrement c'est bien passé
                                               }

                                               //on recharge la page pour ré-initialiser le calendrier avec les infos valides
                                               window.location.reload();

                                           },
                                           error: function () {//le serveur a retourné une erreur 500
                                               window.location.reload();
                                           }
                                        });


                                    } else { //on utilise l'algo ponctuel

                                        //url de routing
                                        var url = Routing.generate('planningvol_savemodificationavionvolponctuelle');

                                        //on affiche un SPinner de chargement
                                        $("#mySpinnerlabel").text("Modification(s) d'avion(s)...");
                                        $("#mySpinnerModal").modal("show");

                                        //on récupére le jour courant
                                        var curentmoment = $('#calendar').fullCalendar('getDate');
                                        saveparam_ajours.push(curentmoment.format('DD-MM-YYYY'));

                                        //On enregistre ces modification en BDD
                                        $.ajax({
                                            url: url,
                                            dataType: 'json',
                                            type: 'POST',
                                            data: {
                                                'arraydatesdelasemaine': saveparam_ajours,
                                                'arrayvolsid': selectedeventsarray,
                                                'ancienavionid': startresourceeventbeingdragged.id,
                                                'nouvelavionid': event.resourceId
                                            },
                                            success: function (doc) {

                                                //On enléve le spinner
                                                $("#mySpinnerModal").modal("hide");
                                                $("#mySpinnerlabel").text("Chargement jour courant...");

                                                var jsonobj = $.parseJSON(doc);

                                                if (jsonobj.success == false) {

                                                    // l'enregistrement des modifications dans la BDD a retournée une erreur, on affiche
                                                    bootbox.alert("<i class='glyphicon glyphicon-alert' style='color: red;'></i> " + jsonobj.erreur,
                                                        function () {
                                                            //on fais une raz
                                                            //on efface les selections de jours hebdomadaire
                                                            $('div[id^="PlHebd_"]').each(function () {
                                                                $(this).removeClass('joursem_sel');
                                                                $(this).children().first().next().remove();
                                                                selectedjoursplanninghebdomadairearray = new Array();
                                                            });

                                                            //on vide le tableau la modif viens d'étre effectuee
                                                            selectedeventsarray = [];

                                                            //on met a jour la var de session
                                                            var curentmoment = $('#calendar').fullCalendar('getDate');
                                                            majPlanningVolParameters(interactionsouris, selectedeventsarray, curentmoment.format('DD-MM-YYYY'), zoom_y, typedemodificationplanning, typedeplanning, periodecustomdebut, periodecustomfin, selectedIdSaison);
                                                            revertFunc(); //on annule le déplacement du vol "draggé"

                                                            //on recharge la page pour ré-initialiser le calendrier avec les infos valides à la fermeture de l'alerte
                                                        //window.location.reload();
                                                        });

                                                } else {

                                                    //l'enregistrement c'est bien passé, on recharge la page pour ré-initialiser le calendrier avec les infos valides
                                                    window.location.reload();

                                                }

                                            },
                                            error: function () {//le serveur a retourné une erreur 500

                                                // l'enregistrement des modifications dans la BDD a retournée une erreur, on affiche
                                                bootbox.alert("<i class='glyphicon glyphicon-alert' style='color: red;'></i> Attention: l\'enregistrement des modifications n\'a put se terminer, le serveur a retourné une erreur 500!",
                                                    function () {

                                                        //on fais une raz
                                                        //on efface les selections de jours hebdomadaire
                                                        $('div[id^="PlHebd_"]').each(function () {
                                                            $(this).removeClass('joursem_sel');
                                                            $(this).children().first().next().remove();
                                                            selectedjoursplanninghebdomadairearray = new Array();
                                                        });

                                                        //on vide le tableau la modif viens d'étre effectuee
                                                        selectedeventsarray = [];

                                                        //on met a jour la var de session
                                                        var curentmoment = $('#calendar').fullCalendar('getDate');
                                                        majPlanningVolParameters(interactionsouris, selectedeventsarray, curentmoment.format('DD-MM-YYYY'), zoom_y, typedemodificationplanning, typedeplanning, periodecustomdebut, periodecustomfin, selectedIdSaison);
                                                        revertFunc(); //on annule le déplacement du vol "draggé"

                                                        //on recharge la page pour ré-initialiser le calendrier avec les infos valides à la fermeture de l'alerte
                                                        window.location.reload();
                                                    });

                                            }
                                        });

                                    }

                                    //on efface les selections de jours hebdomadaire
                                    $('div[id^="PlHebd_"]').each(function () {
                                        $(this).removeClass('joursem_sel');
                                        $(this).children().first().next().remove();
                                        selectedjoursplanninghebdomadairearray = new Array();
                                    });

                                    //on vide le tableau la modif viens d'étre effectuee
                                    selectedeventsarray = [];

                                    //on met a jour la var de session
                                    var curentmoment = $('#calendar').fullCalendar('getDate');
                                    majPlanningVolParameters(interactionsouris, selectedeventsarray, curentmoment.format('DD-MM-YYYY'), zoom_y, typedemodificationplanning, typedeplanning, periodecustomdebut, periodecustomfin,selectedIdSaison);

                                    //on ferme le modal
                                    $('#myMouseSelectionMoveModal').modal('hide');
                                });

                                // NON selectionné
                                $("#mouseSelectionMoveANNULER").bind("click", function () {

                                    //on efface les selections de jours hebdomadaire
                                    $('div[id^="PlHebd_"]').each(function () {
                                        $(this).removeClass('joursem_sel');
                                        $(this).children().first().next().remove();
                                        selectedjoursplanninghebdomadairearray = new Array();
                                    });

                                    //on vide le tableau la modif viens d'étre effectuee
                                    selectedeventsarray = [];

                                    //on met a jour la var de session
                                    var curentmoment = $('#calendar').fullCalendar('getDate');
                                    majPlanningVolParameters(interactionsouris, selectedeventsarray, curentmoment.format('DD-MM-YYYY'), zoom_y, typedemodificationplanning, typedeplanning, periodecustomdebut, periodecustomfin,selectedIdSaison);
                                    revertFunc(); //on annule le déplacement du vol "draggé"

                                    //on ferme le modal
                                    $('#myMouseSelectionMoveModal').modal('hide');
                                });

                            });

                        }

                    }

                } else {

                    // l'enregistrement des modifications dans la BDD a eu un pb, on affiche l'erreur
                    bootbox.alert("<i class='glyphicon glyphicon-alert' style='color: red;'></i> Attention: impossible de déplacer des vols d\'avions différents vers un avion ou il n\'y a pas de plages horaires compatibles avec la totalité des vols selectionnés !",
                        function (){

                            //on fais une raz
                            //on efface les selections de jours hebdomadaire
                            $('div[id^="PlHebd_"]').each(function () {
                                $(this).removeClass('joursem_sel');
                                $(this).children().first().next().remove();
                                selectedjoursplanninghebdomadairearray = new Array();
                            });

                            //on vide le tableau la modif viens d'étre effectuee
                            selectedeventsarray = [];

                            //on met a jour la var de session
                            var curentmoment = $('#calendar').fullCalendar('getDate');
                            majPlanningVolParameters(interactionsouris, selectedeventsarray, curentmoment.format('DD-MM-YYYY'), zoom_y, typedemodificationplanning, typedeplanning, periodecustomdebut, periodecustomfin,selectedIdSaison);
                            revertFunc(); //on annule le déplacement du vol "draggé"

                        });

                }

            }else{
                //on affiche l'erreur
                bootbox.alert("<i class='glyphicon glyphicon-alert' style='color: red;'></i> "+libelleErreurInterdictionDragStart);

                //on réactive l'event
                event.editable = true;

            }


        }

    });


    // =========================================================================================================================
    //                                    FullCalendar Previews Plannings Hebdomadaire
    // =========================================================================================================================

    function createAllHebdomadaireCaldendars(lemomentdujourenedition) {

        //$("#spinnercalendrierhebdomadaire").html('<span style="color:#888888;"><i class="fa fa-spinner fa-spin" style="color:#FBB81F;font-size:2.0em;"></i> Merci de patienter...</span>');
        NProgress.configure({ parent: '#spinnercalendrierhebdomadaire' });
        NProgress.configure({ trickle: true });
        NProgress.configure({ showSpinner: false });

        var numjourenedition = lemomentdujourenedition.format('e');
        var datedetravail;
        var datejourhebdomadairecourantboucle;
        var query;

        //on grise tous les jours du planning hebdomadaire
        $("div[id^='BlockPlanningHebdo_']").css('opacity','0.4');


        setTimeout(function() {


            for (var i = 0; i <= 6; i++) {

                datedetravail = moment($('#calendar').fullCalendar('getDate'), 'YYYY-MM-DD');
                query = eval(i - datedetravail.weekday());
                datejourhebdomadairecourantboucle = datedetravail.add(query, 'days');

                //on enregistre la date dans les data attribute des bouton
                $('#PlHebd_'+i).attr('data-datedecejour',datedetravail.format('DD-MM-YYYY'));

                //on modifie la légende des jour avec la date
                $('#PlHebd_'+i+' span').html(datedetravail.format('ddd DD MMM YYYY'));

                $('#PlH_' + i + '_calendar').fullCalendar({
                    header: false,
                    footer: false,
                    resourceAreaWidth: function(){
                        if (i!=0){
                            return 0;
                        }else{
                            return 34;
                        }
                    }, //largeur de la colonne avions (ici on la cache)
                    slotWidth: largeurDesColonnesPreviewHebdomadaire, //lareur des colonnes horraire 00 - 23
                    locale: 'fr',
                    schedulerLicenseKey: 'GPL-My-Project-Is-Open-Source',
                    now: datejourhebdomadairecourantboucle,
                    editable: false, // disable draggable events
                    height: "auto",
                    scrollTime: '00:00', // undo default 6am scrollTime
                    handleWindowResize: true,
                    titleFormat: 'ddd DD MMM YYYY', //format de la date
                    eventOverlap: false, //empeche le chevauchement de 2 events
                    slotDuration: '01:00', //The length of time each vertical line of the timeline represents. Without this option, a reasonable value will be automatically computed based on the view's total duration. (1 heure)
                    //snapDuration: '99:00', //On bloque le déplacement vertical en mettant un interval géant
                    minTime: '04:00', //Determines the starting time that will be displayed, even when the scrollbars have been scrolled all the way up.
                    maxTime: '24:00', //Determines the end time (exclusively) that will be displayed, even when the scrollbars have been scrolled all the way down.

                    defaultView: 'timelineDay',
                    windowResize: function (view) {

                        //resize hauteur cellules libellés Avions
                        $("div[id^='PlH_'] .fc-body .fc-rows .fc-widget-content").css('height',hauteurCellulesPreviewHebdomadaire+'px');

                        //resize hauteur cellules Vols
                        $("div[id^='PlH_'] div div table tbody tr td.fc-time-area div div div div div table tbody tr td").css('height',hauteurCellulesPreviewHebdomadaire+'px');
                        $("div[id^='PlH_'] div div table tbody tr td.fc-time-area div div div div div table tbody tr td div").css('height',hauteurCellulesPreviewHebdomadaire+'px');
                        $("div[id^='PlH_'] div div table tbody tr td.fc-time-area div div div div div table tbody tr td div div").css('height',hauteurCellulesPreviewHebdomadaire+'px');

                        //resize lageur de la la légende Avions
                        $("#PlH_0_calendar div div table tbody tr td.fc-resource-area").css('width','34px');
                        $("#PlH_0_calendar div div table tbody tr td div div div.fc-scroller-canvas").css('min-width','34px');
                        $("#PlH_0_calendar div div table tbody tr td div div div.fc-scroller-canvas").css('width','auto');

                    },
                    viewRender: function( view, element ) {

                        view.name = i;

                        var cejourestunefinperiodesaison = false;
                        var cejourestundebutperiodesaison = false;
                        var cesperiodessaisons = "";
                        var derniereperiodessaisons = "";

                        // on passe ne revue les periodes saisons
                        $("#saisonselect1 option").each(function () {

                            var tempdatefin = moment($(this).attr('data-datefinperiodesaison'));
                            var tempdatedebut = moment($(this).attr('data-datedebutperiodesaison'));

                            // ce jour est la fin d'une période saison
                            if(moment(datejourhebdomadairecourantboucle.format('YYYY-MM-DD')).isSame(tempdatefin.format('YYYY-MM-DD'))){

                                cejourestunefinperiodesaison = true;
                                cesperiodessaisons += $(this).text()+"\n";
                                derniereperiodessaisons = $(this).text();
                                //$('#infodebutfinsaison_'+i).html('<span style="color:'+couleurInfoTexteDebutFinSaison+';font-weight: bold;font-variant-caps: all-small-caps;">Fin de '+$(this).text()+' <i class="fa fa-flag fa-lg"></i></span>');

                            }else if(moment(datejourhebdomadairecourantboucle.format('YYYY-MM-DD')).isSame(tempdatedebut.format('YYYY-MM-DD'))){
                            // ce jour est le début d'une nouvelle période saison

                                cejourestundebutperiodesaison = true;
                                cesperiodessaisons += $(this).text()+"\n";
                                derniereperiodessaisons = $(this).text();
                                //$('#infodebutfinsaison_'+i).html('<span style="color:'+couleurInfoTexteDebutFinSaison+';font-weight: bold;font-variant-caps: all-small-caps;"><i class="fa fa-flag-checkered fa-lg"></i> Début de '+$(this).text()+'</span>');

                            }

                        });

                        if(cejourestunefinperiodesaison == true){

                            cesperiodessaisons = cesperiodessaisons.substring(0,eval(cesperiodessaisons.length-1));

                            if(derniereperiodessaisons == cesperiodessaisons){
                                $('#infodebutfinsaison_'+i).html('<span style="color:'+couleurInfoTexteDebutFinSaison+';font-weight: bold;font-variant-caps: all-small-caps;">Fin de '+cesperiodessaisons+' <i class="fa fa-flag fa-lg"></i></span>');
                            }else{
                                $('#infodebutfinsaison_'+i).html('<span style="color:'+couleurInfoTexteDebutFinSaison+';font-weight: bold;font-variant-caps: all-small-caps;">Fin de '+derniereperiodessaisons+' <button type="button" class="btn btn-default btn-xs" data-toggle="tooltip" data-delay="0" data-placement="left" title="Le '+datejourhebdomadairecourantboucle.format('DD MMM YYYY')+'\n fin des saisons:'+'\n\n'+cesperiodessaisons+'" style="margin:0px;padding:0px;"><i class="fa fa-info-circle fa-lg" style="font-size:1.25em;color:#6EACCC;margin-bottom:5px;"></i></button> <i class="fa fa-flag fa-lg"></i></span>');
                            }

                        }

                        if(cejourestundebutperiodesaison == true) {

                            cesperiodessaisons = cesperiodessaisons.substring(0, eval(cesperiodessaisons.length - 1));

                            if (derniereperiodessaisons == cesperiodessaisons) {
                                $('#infodebutfinsaison_'+i).html('<span style="color:'+couleurInfoTexteDebutFinSaison+';font-weight: bold;font-variant-caps: all-small-caps;"><i class="fa fa-flag-checkered fa-lg"></i> Début de '+cesperiodessaisons+'</span>');
                            } else {
                                $('#infodebutfinsaison_'+i).html('<span style="color:'+couleurInfoTexteDebutFinSaison+';font-weight: bold;font-variant-caps: all-small-caps;"><i class="fa fa-flag-checkered fa-lg"></i> Début de '+derniereperiodessaisons+' <button type="button" class="btn btn-default btn-xs" data-toggle="tooltip" data-delay="0" data-placement="left" title="Le '+datejourhebdomadairecourantboucle.format('DD MMM YYYY')+'\n début des saisons:'+'\n\n'+cesperiodessaisons+'" style="margin:0px;padding:0px;"><i class="fa fa-info-circle fa-lg" style="font-size:1.25em;color:#6EACCC;margin-bottom:5px;"></i></button></span>');
                            }

                        }

                    },
                    dayRender: function (date, cell) { //cellules heure/vol

                        cell.css("width", largeurDesColonnesPreviewHebdomadaire+"px");

                        var jouraujourdhui = moment();

                        //couleur de fond des cellules du planning
                        if(parseInt(date.format('H'))%2 == 0){

                            if(moment(datejourhebdomadairecourantboucle.format('YYYY-MM-DD')).isSameOrAfter(jouraujourdhui.format('YYYY-MM-DD'))){
                                //si ce n'est pas un samedi ou un dimanche
                                if( (date.format('e') != 5) && (date.format('e') != 6) ) {
                                    cell.css("background-color", couleursAlternativeFondPlanningJoursFuturs[0]);
                                }else {
                                    //si c'est un samedi ou un dilmanche
                                    cell.css("background-color", couleursAlternativeFondPlanningJoursWeekend[0]);
                                }
                            }else{// dates passées du planning
                                cell.css("background-color", couleursAlternativeFondPlanningJoursPasses[0]);
                            }

                        }else{

                            if(moment(datejourhebdomadairecourantboucle.format('YYYY-MM-DD')).isSameOrAfter(jouraujourdhui.format('YYYY-MM-DD'))){
                                //si ce n'est pas un samedi ou un dimanche
                                if( (date.format('e') != 5) && (date.format('e') != 6) ) {
                                    cell.css("background-color", couleursAlternativeFondPlanningJoursFuturs[1]);
                                }else{
                                    //si c'est un samedi ou un dilmanche
                                    cell.css("background-color", couleursAlternativeFondPlanningJoursWeekend[1]);
                                }
                            }else{// dates passées du planning
                                cell.css("background-color", couleursAlternativeFondPlanningJoursPasses[1]);
                            }

                        }


                    },
                    resourceLabelText: 'Avions / Heures',
                    resources: function (callback) { //les avions

                        //on enlève la ligne de resize des resources
                        $('.fc-col-resizer').hide();

                        callback(lesresources.slice(0,12)); //on retourne les avions du planning principal (identique pour tous les plannings)

                    },
                    resourceRender: function(resourceObj, labelTds, bodyTds) {

                        labelTds.html(resourceObj.title.split(" ")); //nom de l'avion
                        labelTds.css('font-size', '0.40em');
                        labelTds.css('color',couleurTexteCellulesAvions);

                        //hauteru de la cellule de légende avions
                        labelTds.css('height',hauteurCellulesPreviewHebdomadaire+'px');

                        if (i==0) {
                            //couleur de fond des cellules avions
                            //labelTds.css('background', couleurDeFondCellulesAvions);
                            if(resourceObj.codecompagnie == "XK"){ //avion appartenant à AirCorsica
                                if(resourceObj.type == "AT7"){ //les AT7 d'AirCorsica
                                    labelTds.css('background', couleurDeFondCellulesAvions_XK_AT7);
                                }else{ //les autres avions d'AirCorsica A320
                                    labelTds.css('background', couleurDeFondCellulesAvions_XK_320);
                                }
                            }else{ //avion affrété
                                labelTds.css('background', couleurDeFondCellulesAvions_Affrete);
                            }
                        }

                    },
                    events: function (start, end, timezone, callback) { //les vols

                        var leseventsdupetitplanning = [];

                        //on récupére les events de la BDD
                        var url = Routing.generate('planningvol_gettodayvolsasjson');

                        $.ajax({
                            url: url,
                            dataType: 'json',
                            type: 'POST',
                            data: {
                                'start': start.unix(),
                                'end': end.unix()
                            },
                            success: function(doc) {

                                nbHebdoPreviewDaysDataLoaded++;

                                var jsonobjarray = $.parseJSON(doc);

                                jsonobjarray.forEach(function(uneventjson) {

                                    if(uneventjson.immobilisation){

                                        leseventsdupetitplanning.push({

                                            id: uneventjson.id,
                                            resourceId: uneventjson.resourceId,
                                            start: uneventjson.start,
                                            end: uneventjson.end,
                                            immobilisation: uneventjson.immobilisation,
                                            title: uneventjson.title,
                                            causeimmobilisation: uneventjson.causeimmobilisation,
                                            periodeimmobilisation: uneventjson.periodeimmobilisation,
                                            textColor: uneventjson.textColor,
                                            borderColor: uneventjson.borderColor,
                                            className: uneventjson.className,
                                            durationEditable: false,
                                            overlap: false,
                                            editable: false

                                        });

                                    }else {

                                        leseventsdupetitplanning.push({

                                            id: uneventjson.id,
                                            resourceId: uneventjson.resourceId,
                                            start: uneventjson.start,
                                            chevauchejour: uneventjson.chevauchejour,
                                            end: uneventjson.end,
                                            villeDepart: uneventjson.villeDepart,
                                            villeArrivee: uneventjson.villeArrivee,
                                            codeVol: uneventjson.codeVol,
                                            typeVol: uneventjson.typeVol,
                                            title: uneventjson.title,
                                            textColor: uneventjson.textColor,
                                            backgroundColor: uneventjson.backgroundColor,
                                            borderColor: uneventjson.borderColor,
                                            durationEditable: uneventjson.durationEditable,
                                            editable: false,
                                            avecUnAvionImmobilise: uneventjson.avecUnAvionImmobilise

                                        });
                                    }
                                });

                                callback(leseventsdupetitplanning);
                            },
                            error: function(){
                            }
                        });


                    },
                    eventRender: function (event, element) { //rendu d'un vol

                        var htmlContent = '';

                        if (event.immobilisation) {

                            htmlContent = '<div class="fc-content" style="height:' + hauteurCellulesPreviewHebdomadaire + 'px;margin-left:-2px; padding-right:4px; margin-top:-5px; background: repeating-linear-gradient(45deg,'+couleursAlternativeFondAvionImmobilise[0]+','+couleursAlternativeFondAvionImmobilise[0]+' 1px,'+couleursAlternativeFondAvionImmobilise[1]+' 1px,'+couleursAlternativeFondAvionImmobilise[1]+' 2px);"></div>';

                        } else {
                            htmlContent = '<div class="fc-content" style="height:' + hauteurCellulesPreviewHebdomadaire + 'px;margin-left:-5px;margin-right:2px;"></div>';
                        }

                        element.html(htmlContent);

                    },
                    eventAfterRender: function( event, element, view ) {

                        //cas d'un event sur un avion immobilisé (vol d'essai par exemple)
                        if (event.avecUnAvionImmobilise == "true") {
                            //on verifie que l'element jquery ne se trouve pas déja dans le tableau
                            var elementjquerydejaenregistrerdansletableau = false;
                            for (var i = 0; i < aJQueryElementsVolsAvecUnAvionImmobilise.length; i++) {
                                if (aJQueryElementsVolsAvecUnAvionImmobilise[i] === element) {
                                    elementjquerydejaenregistrerdansletableau = true;
                                    break;
                                }
                            }
                            if (elementjquerydejaenregistrerdansletableau == false) {
                                aJQueryElementsVolsAvecUnAvionImmobilise.push(element);
                            }
                        }
                    },
                    eventAfterAllRender: function (view) { //aprés tous les rendus

                        //cas d'un event sur un avion immobilisé (vol d'essai par exemple)
                        aJQueryElementsVolsAvecUnAvionImmobilise.forEach(function(element) {
                            //on le positionne sur l'avion immobilisé
                            element.css('top','0px');
                            element.css('z-index','1000');
                        });
                        aJQueryElementsVolsAvecUnAvionImmobilise = new Array();

                        // -------------
                        // cellules vols
                        // -------------

                        //on fixe la hauteur du contenu des cellules
                        $("div[id^='PlH_'] tr[data-resource-id] td").children().css("height", hauteurCellulesPreviewHebdomadaire + "px");
                        $("div[id^='PlH_'] tr[data-resource-id] td").children().children().css("height", hauteurDuContenuDesCellulesResumeHebdomadaire + "px");
                        $("div[id^='PlH_'] tr[data-resource-id] td").children().children().children().removeAttr('class');
                        $("div[id^='PlH_'] tr[data-resource-id] td").children().children().children().attr('class', 'fc-timeline-event');
                        $("div[id^='PlH_'] tr[data-resource-id] td").children().children().children().css('bottom', '3px');

                        //pn efface la première ligne de légende
                        $("div[id^='PlH_'] .fc-head").css("display", "none");

                        // ---------------
                        // cellules Avions
                        // ---------------

                        $("#PlH_0_calendar .fc-resource-area").css("background-color", "#303D45"); //couleur de fond de la zone ascensseur horizontal des avions

                        //on redimensionne la largeur de la la légende Avions
                        $("#PlH_0_calendar div div table tbody tr td.fc-resource-area").css('width','34px');
                        $("#PlH_0_calendar div div table tbody tr td div div div.fc-scroller-canvas").css('min-width','34px');
                        $("#PlH_0_calendar div div table tbody tr td div div div.fc-scroller-canvas").css('width','auto');

                        //on enleve la couleur de fond et l'encadrement des petit calendrier
                        $("div[id^='PlH_'] .fc-body").children().children().css('border','0px');
                        $("div[id^='PlH_'] .fc-body").children().children().css('background-color','#ffffff');
                        $("div[id^='PlH_'] .fc-body").children().children().next().next().css('border','0px');
                        $("div[id^='PlH_'] .fc-body").children().children().next().next().children().css('background-color','#ffffff');

                        //on affiche les planning deja chargé en grisé
                        $("#bloccalendrierhebdomadaire").css("height","");
                        $('#leplanninghebdomadaire').css('opacity','1.0');
                        $('#BlockPlanningHebdo_'+view.name).css('opacity','1.0');

                        //Gestion de la barre de chargement en haut de page
                        if($("#mySpinnerModal").is(':visible')){
                            $("#mySpinnerModal").modal("hide");
                        }

                        $("#spinnercalendrierhebdomadaire").html('<div class="text-center" style="margin-right:35%;"><div style="float:right;font-size:1.0em;padding-top:8px;margin-right:10px;">Génération planning jour n°'+nbHebdoPreviewDaysDataLoaded+' sur 7 </div><div class="loaderHebdo" style="float:right;margin-right:10px;margin-top:3px;"></div></div>');
                        if(nbHebdoPreviewDaysDataLoaded<7){
                            //on incémente la barre de 1/7 soit 0.14
                            NProgress.inc(0.125);
                        }else{
                            //tous les jours ont été chargés
                            NProgress.done();
                            nbHebdoPreviewDaysDataLoaded = 0;
                            $("#spinnercalendrierhebdomadaire").html(' ');

                            //on selectionne le jour de la semaine du planning hebdomadaire
                            var curentmoment = $('#calendar').fullCalendar('getDate');
                            $("#PlHebd_"+curentmoment.format('e')).addClass('badge');
                            $("#PlHebd_"+curentmoment.format('e')).addClass('badge-success');


                        }

                    }

                });


            }




        },100);



    }

    function destroyAllHebdomadaireCaldendars() {

        for (var i = 0; i <= 6; i++) {
            $('#PlH_'+i+'_calendar').fullCalendar('destroy');
        }

        $('#leplanninghebdomadaire').css('opacity','1.0');
        NProgress.done();
        nbHebdoPreviewDaysDataLoaded = 0;
        $("#spinnercalendrierhebdomadaire").html(' ');

    }


})(jQuery);

