function bindAddAdresseToTable(){

    $("#adresse_site_to_table").click(function(e) {

        var numero_vol = $("#numero_vol").val();
        var type_message = $("#type_message").val();
        var inc_type_message = $("#inc_type_message").val();
        var nbr_mess_asmssm = $("#nbr_mess_asmssm").val();

        e.preventDefault();

        $.each( $('.id_adresse_sita:checked'),function (e) {

            var adresse_sita = $(this).attr('data-addrSita');
            var email_sita = $(this).attr('data-emailSita');
            var adresse_sita_libelle = $(this).attr('data-addrlibelleSita');
            var adresse_sita_groupe = $(this).attr('data-addrGroupeSita');
            var adresse_sita_coordinateur = $(this).attr('data-addrCoordSita');

            if (adresse_sita_coordinateur){
                var coordinateur = adresse_sita_coordinateur;
            }else{
                var coordinateur = "-";
            }

            var microtime;

            var ligne_tableau="";

            //Dans le cas ou on a un message de type ASM, on compte cb il y en a pour ajouter les adresses
            //selectionnées pour autant de message ASM existant
            if (type_message=="ASM"){

                i=0;

                //on boucle sur le nombre de ASM trouvé et on crée la ligne
                while (i < nbr_mess_asmssm) {

                    var display="";

                    /*$.ajax({
                        type: "POST",
                        async: false,
                        global: false,
                        url: Routing.generate('vol_messagerie_setnewmicrotime'),
                        success: function (data) {
                            microtime = data;
                        }
                    });*/

                    microtime = $("#tableau_adresses_SITA").find('tr[class="iddestinatairesbloc_'+numero_vol+'_ASM_'+i+'"]').first().children().children().attr('data-idALTEAMessage');


                    if(inc_type_message != i){
                        display = 'style="display:none;"';
                    }

                    ligne_tableau = '<tr class="iddestinatairesbloc_'+numero_vol+'_ASM_'+i+'" '+display+'> \n\
                                    <td>\n\
                                        <input type="checkbox" class="destinatairechk" \n\
                                                data-newDestinataire="true" \n\
                                                data-idVol="'+numero_vol+'" \n\
                                                data-libelle="'+adresse_sita_libelle+'" \n\
                                                data-groupe="'+adresse_sita_groupe+'" \n\
                                                data-adresseSITA="'+adresse_sita+'" \n\
                                                data-emailSITA="'+email_sita+'" \n\
                                                data-coordinateur="'+coordinateur+'" \n\
                                                data-typeMassage="'+type_message+'" data-idALTEAMessage="'+microtime+'" checked/></td>\n\
                                    <td>'+adresse_sita+'</td> \n\
                                    <td>'+email_sita+'</td> \n\
                                    <td>'+adresse_sita_groupe+'</td> \n\
                                    <td>'+adresse_sita_libelle+'</td> \n\
                                    <td>'+coordinateur+'</td></tr>';

                    $("body #tableau_adresses_SITA").append(ligne_tableau);
                    console.log(ligne_tableau);

                    i++;
                }

            }else{

                //Sinon, on récupére les infos pour création ligne SSM ou SCR

                /*$.ajax({
                    type: "POST",
                    async: false,
                    global: false,
                    url: Routing.generate('vol_messagerie_setnewmicrotime'),
                    success: function (data) {
                        microtime = data;
                    }
                });*/

                microtime = $("#tableau_adresses_SITA").find('tr[class="iddestinatairesbloc_'+numero_vol+'_'+type_message+'_'+inc_type_message+'"]').first().children().children().attr('data-idALTEAMessage');

                var ligne_tableau = '<tr class="iddestinatairesbloc_'+numero_vol+'_'+type_message+'_'+inc_type_message+'"> \n\
                                    <td>\n\
                                        <input type="checkbox" class="destinatairechk" \n\
                                                data-newDestinataire="true" \n\
                                                data-idVol="'+numero_vol+'" \n\
                                                data-libelle="'+adresse_sita_libelle+'" \n\
                                                data-groupe="'+adresse_sita_groupe+'" \n\
                                                data-adresseSITA="'+adresse_sita+'" \n\
                                                data-emailSITA="'+email_sita+'" \n\
                                                data-coordinateur="'+coordinateur+'" \n\
                                                data-typeMassage="'+type_message+'" data-idALTEAMessage="'+microtime+'" checked/></td>\n\
                                    <td>'+adresse_sita+'</td> \n\
                                    <td>'+email_sita+'</td> \n\
                                    <td>'+adresse_sita_groupe+'</td> \n\
                                    <td>'+adresse_sita_libelle+'</td> \n\
                                    <td>'+coordinateur+'</td></tr>';

                $("body #tableau_adresses_SITA").append(ligne_tableau);

                console.log(ligne_tableau);
            }

        });

        $('#myModal .modal-header .close').click();

    });
}



$(function(){

    $('#ajouterundestinataire').on('click',function(){

        var id_vol = $("#numero_vol_destinataire").val();
        var type_message = $("#type_message_destinataire").val();
        var inc_type_message = $("#inc_type_message_destinataire").val();
        var nbr_mess_asmssm = $("#nbr_asmssm_"+id_vol).val();
        var microtime_message =

        $.ajax({
            type: "GET",
            url: Routing.generate('vol_messagerie_add_adresse_sita',{id_vol:id_vol,type_message:type_message,inc_type_message:inc_type_message,nbr_mess_asmssm:nbr_mess_asmssm}),
            success: function (data) {
                $('#myModal .modal-body').html(data);
                bindAddAdresseToTable();
            },
            beforeSend: function () {
                $('#myModal').modal('toggle');
                $('#myModal .modal-body').html("<div class='progress' id='loadingDiv'><div class='progress-bar progress-bar-striped progress-bar-success active' role='progressbar' \n\
                                         aria-valuenow='100' aria-valuemin='0' aria-valuemax='100' style='width: 100%'></div></div>");
            }
        });

    });

    //au chargement de la page on selectionne le premoier vol de la liste
    $( document ).ready(function() {
        $('.idvolclickzone').first().click();
    });

    if($('iframe').length){
        activaTab('tab1_2');
    }

    $("#cochedecochevols").on('click',function(){

            if( $('input[class=idvolchk]').is(':checked') ){
                $('.idvolchk').prop( "checked", false );
            }else{
                $('.idvolchk').prop( "checked", true );
            }

    });

    $("#cochedecochedestinataires").on('click',function(){
        var findchkornot = 99;
        $('tr[class^=iddestinatairesbloc_]').each( function() {
            if ($(this).css('display') != "none") {

                if (findchkornot == 99){
                    if( $(this).children().children().is(':checked') ) { findchkornot = 1;}else{ findchkornot = 0;}
                }
                switch(findchkornot) {
                    case 0:
                        $(this).children().children().prop( "checked", true );
                        break;
                    case 1:
                        $(this).children().children().prop("checked", false);
                        break;
                }

                /*
                // code pour inversé la selection au cas ou
                if( $(this).children().children().is(':checked') ) {
                    $(this).children().children().prop("checked", false);
                }else {
                    $(this).children().children().prop( "checked", true );
                }
                */

            }
        });

    });



    //$('input[class=idvolchk]').on('click', function(){
    //
    //    if( $(this).is(':checked') ){
    //        //TODO show the messages list for this vol
    //        $(".idvolmessage_"+$(this).attr('id').split('_')[1]).css("display","");
    //
    //    }else{
    //        //TODO hide the messages list for this vol
    //        $(".idvolmessage_"+$(this).attr('id').split('_')[1]).css("display","none");
    //    }
    //
    //});



    //$('input[id^=idmessagechk_]').on('click', function(){
    //
    //        $('tr[class^=iddestinatairesbloc_]').css("display","none");
    //
    //        $('input[id^=idmessagechk_]:checked').each(function(){
    //            $(".iddestinatairesbloc_"+$(this).attr('id').split('_')[1]+"_"+$(this).attr('id').split('_')[2]+"_"+$(this).attr('id').split('_')[3]).css("display","");
    //        });
    //
    //});

    // Enregistrer la modification d'un message
    $('#enregistrermodificationmessage').on('click', function(){

        // on fait confirmer la/les modifications par l'utilisateur
        // --------------------------------------------------------
        bootbox.confirm({
            message: 'Attention, êtes-vous sûr de vouloir enregistrer les modifications de ce message ?',
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
                if (result) {// OUI selectionné

                    var url = Routing.generate('vol_majpartietextemessagealtea');
                    var idvol = $("#"+$('#textearea_emptybackup').attr('data-msgbackupid')).attr('data-idvol');
                    var typemessage = $("#"+$('#textearea_emptybackup').attr('data-msgbackupid')).attr('data-typemassage');
                    var idalteamessage = $("#"+$('#textearea_emptybackup').attr('data-msgbackupid')).attr('data-idalteamessage');
                    var textemessagecorps = $("#"+$('#textearea_emptybackup').attr('data-msgbackupid')).val();

                    //on modifie la variable de session du templatecourant
                    $.ajax({
                        url: url, /*'http://'+window.location.hostname+$('#ajaxmethodsurls').attr('data-setparametresplanningvol-route'),*/
                        dataType: 'json',
                        type: 'POST',
                        data: {
                            idVol: idvol,
                            typeMessage: typemessage,
                            idALTEAMessage: idalteamessage,
                            texteMessageCorps: textemessagecorps
                        },
                        success: function(doc) {
                            //alert("success!! ");
                            bootbox.alert("<i class='glyphicon glyphicon-info-sign' style='color: royalblue;'></i> La modification du message a été effectué.");
                        },
                        error: function(){
                            //alert("error!! ");
                            bootbox.alert("<i class='glyphicon glyphicon-alert' style='color: red;'></i> Erreur d\'enregistrement.");
                        }
                    });

                } else {// NON selectionné

                    //on annule cette modification
                    if($('#textearea_emptybackup').val() != ""){
                        $("#"+$('#textearea_emptybackup').attr('data-msgbackupid')).val($('#textearea_emptybackup').val());
                    }

                    $('#textearea_emptybackup').val("");
                    $('#textearea_emptybackup').attr("data-msgbackupid","");

                    $("#annulermodificationmessage").removeClass("btn-success");
                    $("#annulermodificationmessage").addClass("btn-disabled");
                    $("#annulermodificationmessage").addClass("disabled");
                    $("#enregistrermodificationmessage").removeClass("btn-danger");
                    $("#enregistrermodificationmessage").addClass("btn-disabled");
                    $("#enregistrermodificationmessage").addClass("disabled");

                }
            }
        });

    });

    // Selection d'un vol et Affichages de ces messages
    $('.idvolclickzone').on('click', function(){

        var idvolclicked = $(this).attr('data-clkznvolid').split('_')[1];

        $('#numero_vol_destinataire').val(idvolclicked);

        //on efface les messages des vols non selectionné
        $('tr[class^=idvolmessage_]').css("display","none");

        $('textarea[id^=textearea_]').each( function(){
            if( $(this).css('display') != "none" ){
                if($('#textearea_emptybackup').val() != ""){
                    $("#"+$('#textearea_emptybackup').attr('data-msgbackupid')).val($('#textearea_emptybackup').val());
                }
            }
        });

        $("#annulermodificationmessage").removeClass("btn-success");
        $("#annulermodificationmessage").addClass("btn-disabled");
        $("#annulermodificationmessage").addClass("disabled");
        $("#enregistrermodificationmessage").removeClass("btn-danger");
        $("#enregistrermodificationmessage").addClass("btn-disabled");
        $("#enregistrermodificationmessage").addClass("disabled");

        $('#textearea_emptybackup').val("");
        $('#textearea_emptybackup').attr("data-msgbackupid","");

        if( $(this).parent().css('background-color') == "rgb(255, 255, 255)" ){

            //on efface tous les fond colorés des vols
            $('.idvolbloc').css('background-color',"rgb(255, 255, 255)");
            $('.idvolbloc').css('color',"#000000");

            //$('.idvolbloc').css('background-color',"rgb(255, 255, 255)");
            //$('.idvolbloc').css('color',"#000000");
            $(this).parent().css('background-color',"rgb(41, 169, 229)");
            $(this).parent().css('color',"#FFFFFF");

            $(".idvolmessage_"+idvolclicked).css("display","");
            $('tr[id^=idmessagebloc_'+idvolclicked).first().click();
            //$(".idvolmessage_"+$(this).parent().children().children().attr('id').split('_')[1]).css("display","");
            //$('tr[id^=idmessagebloc_'+$(this).parent().children().children().attr('id').split('_')[1]+']').first().click();

        }else{

            $('.idvolbloc').css('background-color',"rgb(255, 255, 255)");
            $('.idvolbloc').css('color',"#000000");

            $('tr[id^=idmessagebloc_]').css('background-color',"rgb(255, 255, 255)");

            $(".idvolmessage_"+idvolclicked).css("display","none");
            //$(".idvolmessage_"+$(this).parent().children().children().attr('id').split('_')[1]).css("display","none");

            $('textarea[id^=textearea_]').css("display","none");
            $('#textearea_emptybackup').css("display","");

            $('tr[class^=iddestinatairesbloc_]').css("display","none");
            $("#cochedecochedestinataires").removeClass("btn-default");
            $("#cochedecochedestinataires").addClass("btn-disabled");
            $("#cochedecochedestinataires").addClass("disabled");
            $("#ajouterundestinataire").removeClass("btn-success");
            $("#ajouterundestinataire").addClass("btn-disabled");
            $("#ajouterundestinataire").addClass("disabled");

        }

    });

    //on rentre en édition d'un message
    $('textarea[id^=textearea_]').on('focus', function(){

        $('#textearea_emptybackup').val($(this).val());
        $('#textearea_emptybackup').attr('data-msgbackupid',$(this).attr('id'));

    });

    //on est en train de saisir du texte
    $('textarea[id^=textearea_]').keyup(function () {

        $("#annulermodificationmessage").removeClass("btn-disabled");
        $("#annulermodificationmessage").removeClass("disabled");
        $("#annulermodificationmessage").addClass("btn-success");
        $("#enregistrermodificationmessage").removeClass("btn-disabled");
        $("#enregistrermodificationmessage").removeClass("disabled");
        $("#enregistrermodificationmessage").addClass("btn-danger");

    });

    //on click sur l'action annuler la modif du message
    $("#annulermodificationmessage").on('click', function(){

        if($('#textearea_emptybackup').val() != ""){
            $("#"+$('#textearea_emptybackup').attr('data-msgbackupid')).val($('#textearea_emptybackup').val());
        }

        $('#textearea_emptybackup').val("");
        $('#textearea_emptybackup').attr("data-msgbackupid","");

        $("#annulermodificationmessage").removeClass("btn-success");
        $("#annulermodificationmessage").addClass("btn-disabled");
        $("#annulermodificationmessage").addClass("disabled");
        $("#enregistrermodificationmessage").removeClass("btn-danger");
        $("#enregistrermodificationmessage").addClass("btn-disabled");
        $("#enregistrermodificationmessage").addClass("disabled");

    });

    //on click sur l'action enregistrer la modif du message
    $("#enregistrermodificationmessage").on('click', function(){

        $('#textearea_emptybackup').val("");
        $('#textearea_emptybackup').val($("#"+$('#textearea_emptybackup').attr('data-msgbackupid')).val());

        $("#annulermodificationmessage").removeClass("btn-success");
        $("#annulermodificationmessage").addClass("btn-disabled");
        $("#annulermodificationmessage").addClass("disabled");
        $("#enregistrermodificationmessage").removeClass("btn-danger");
        $("#enregistrermodificationmessage").addClass("btn-disabled");
        $("#enregistrermodificationmessage").addClass("disabled");

    });


    // Selection d'un message à afficher
    $('tr[id^=idmessagebloc_]').on('click', function(){

        $('#type_message_destinataire').val($(this).attr('id').split('_')[2]);
        $('#inc_type_message_destinataire').val($(this).attr('id').split('_')[3]);

        if( $(this).css('background-color') == "rgb(255, 255, 255)" ){

            $('tr[id^=idmessagebloc_]').css('background-color',"rgb(255, 255, 255)");
            $('tr[id^=idmessagebloc_]').css('color',"#000000");

            $(this).css('background-color',"rgb(41, 169, 229)");
            $(this).css('color',"#FFFFFF");

            $('textarea[id^=textearea_]').css("display","none");
            $("#libelleinfomsgedite").text($(this).children().text()+' associé au vol '+$("#volnumero_"+$(this).attr('id').split('_')[1]).text());
            $("#textearea_"+$(this).attr('id').split('_')[1]+"_"+$(this).attr('id').split('_')[2]+"_"+$(this).attr('id').split('_')[3]).css("display","");

            //$("#blocbtnenregistrermessage").css("display","");

            $('tr[class^=iddestinatairesbloc_]').css("display","none");
            $("#libelleinfodestinatairegedite").text('des messages '+$(this).children().text().split(':')[0]+' associé au vol '+$("#volnumero_"+$(this).attr('id').split('_')[1]).text());
            $(".iddestinatairesbloc_"+$(this).attr('id').split('_')[1]+"_"+$(this).attr('id').split('_')[2]+"_"+$(this).attr('id').split('_')[3]).css("display","");
            $("#cochedecochedestinataires").removeClass("btn-disabled");
            $("#cochedecochedestinataires").removeClass("disabled");
            $("#cochedecochedestinataires").addClass("btn-default");
            $("#ajouterundestinataire").removeClass("btn-disabled");
            $("#ajouterundestinataire").removeClass("disabled");
            $("#ajouterundestinataire").addClass("btn-success");

        }else{

            $('tr[id^=idmessagebloc_]').css('background-color',"rgb(255, 255, 255)");
            $('tr[id^=idmessagebloc_]').css('color',"#000000");

            $("#libelleinfomsgedite").text("");
            $('textarea[id^=textearea_]').css("display","none");
            $('#textearea_emptybackup').css("display","");

            //$("#blocbtnenregistrermessage").css("display","none");

            $('tr[class^=iddestinatairesbloc_]').css("display","none");
            $("#cochedecochedestinataires").removeClass("btn-default");
            $("#cochedecochedestinataires").addClass("btn-disabled");
            $("#cochedecochedestinataires").addClass("disabled");
            $("#ajouterundestinataire").removeClass("btn-success");
            $("#ajouterundestinataire").addClass("btn-disabled");
            $("#ajouterundestinataire").addClass("disabled");
        }

    });

    //Action acquiter les vols selectionnés
    $("#btnacquitter").on('click', function(e) {
        e.preventDefault();

        var idsString = "";
        $('input[class=idvolchk]:checked').each(function(){
            idsString += $(this).attr('id').split('_')[1]+'_';
        });

        if('' == idsString){
            bootbox.alert("<i class='glyphicon glyphicon-alert' style='color: red;'></i> Vous devez cocher au minimum une ligne de vols.");
            return false;
        }

        //on enleve le dernier "_"
        idsString = idsString.slice(0, -1);

        var url = Routing.generate('vol_acquitter');

        //on modifie la variable de session du templatecourant
        $.ajax({
            url: url,
            dataType: 'json',
            type: 'POST',
            data: {
                arrayIdVolsAAcquitter: idsString
            },
            success: function(doc) {
                //alert('success');

                $('[class^=idvolmessage_]').css("display","none");
                $('textarea[id^=textearea_]').css("display","none");
                $("#blocbtnenregistrermessage").css("display","none");
                $('tr[class^=iddestinatairesbloc_]').css("display","none");
                $(".idvolbloc").css("display","none");

                //redirection sur la page précédentes avec les mêmes filtres choisis
                var temp = $("#paramsListeVolsFilter").val().replace("%5B%5D", "[]");
                var tempParams = temp.split("&");
                var params = [];
                tempParams.forEach(function(currentValue, index, arr){
                    var tempVars = currentValue.split("=");
                    if(tempVars[0]=="jours%5B%5D"){tempVars[0] = "jours[]"+tempVars[1]} //correction du bug Patrice les différents jour ont le même nom de variables
                    params[tempVars[0]] = tempVars[1];
                });

                window.location.href = Routing.generate('vol_liste',params);

            },
            error: function(){
                //alert("error!! ");
                bootbox.alert("<i class='glyphicon glyphicon-alert' style='color: red;'></i> Erreur d\'acquittement.");
            }
        });


    });

    //Action expediez les messages
    $("#btnexpedier").on('click', function(e) {

        e.preventDefault();

        //liste des vols non selectionné
        //------------------------------
        var idsVolNotCheckedString = "";
        $('input[class=idvolchk]').each(function(){
            if(!$(this).is(':checked')){
                idsVolNotCheckedString += $(this).attr('id').split('_')[1]+'_';
            }
        });
        if(idsVolNotCheckedString!="") {
            //on enleve le dernier "_"
            idsVolNotCheckedString = idsVolNotCheckedString.slice(0, -1);
        }

        //liste des vols selectionnés
        //---------------------------
        var idsVolCheckedString = "";
        var aidsVolChecked = new Array();
        $('input[class=idvolchk]:checked').each(function(){
            idsVolCheckedString += $(this).attr('id').split('_')[1]+'_';
            aidsVolChecked.push($(this).attr('id').split('_')[1]);
        });

        if('' == idsVolCheckedString){
            bootbox.alert("<i class='glyphicon glyphicon-alert' style='color: red;'></i> Vous devez cocher au minimum une ligne de vols.");
            return false;
        }

        //on enleve le dernier "_"
        idsVolCheckedString = idsVolCheckedString.slice(0, -1);

        var amodifsdestinataires = new Object();
        /* c'est un tableau associatif qui aura une structure de type:
        [idVol1]
            [idMessageALTEA1]
                [aAdressesSITAaSupprimer] //les destinataires deselectionné (à supprimer de la liste d'envoi dans le php)
                    AJADFXK
                    AJAKAAF
                    HOPSPA5
                    ...etc
                [aAdressesSITAaAjouter] //les destinataires créer/ajouter (à rajouter à la liste d'envoi dans le php)
                    QVI01AF
                    ...etc
            [idMessageALTEA2]
                [aAdressesSITAaSupprimer]
                    ...etc
                [aAdressesSITAaAjouter]
                    ...etc
         [idVol2]
             ...etc
        */

        //Pour chaque destinataires
        $('tr[class^=iddestinatairesbloc_]').each( function() {//pour tous les messages de chaque vol

            if (aidsVolChecked.indexOf($(this).children().children().attr('data-idVol')) > -1) {// l'id vol de ce message correspond à un vol selectionné pour l'expedition des messages

                var idV = $(this).children().children().attr('data-idVol');
                var typmsg = $(this).children().children().attr('data-typeMassage');
                if(typmsg!="SCR"){typmsg="ASMSSM";}
                var idaltea = $(this).children().children().attr('data-idALTEAMessage');
                var addrsita = $(this).children().children().attr('data-adresseSITA');
                var emailsita = $(this).children().children().attr('data-emailSITA');
                var libellesita = $(this).children().children().attr('data-libelle');
                var groupesita = $(this).children().children().attr('data-groupe');
                var coordinateursita = $(this).children().children().attr('data-coordinateur');


                if( $(this).children().children().is(':checked') == false ) {// la case n'est pas coché c'est un destinataire à supprimer

                    if (typeof amodifsdestinataires[idV] == "undefined"){
                        amodifsdestinataires[idV] = new Object();
                    }

                    if (typeof amodifsdestinataires[idV][typmsg] == "undefined"){
                        amodifsdestinataires[idV][typmsg] = new Object();
                    }

                    if (typeof amodifsdestinataires[idV][typmsg][idaltea] == "undefined"){
                        amodifsdestinataires[idV][typmsg][idaltea] = new Object();
                    }

                    if (typeof amodifsdestinataires[idV][typmsg][idaltea]["aAdressesSITAaSupprimer"] == "undefined"){
                        amodifsdestinataires[idV][typmsg][idaltea]["aAdressesSITAaSupprimer"] = new Array();
                    }

                    amodifsdestinataires[idV][typmsg][idaltea]["aAdressesSITAaSupprimer"].push(addrsita+"#"+emailsita);


                }else{// la case est coché

                    if( $(this).children().children().attr('data-newDestinataire') == "true" ){// c'est un destinataire qui vient d'être créer

                        if (typeof amodifsdestinataires[idV] == "undefined"){
                            amodifsdestinataires[idV] = new Object();
                        }

                        if (typeof amodifsdestinataires[idV][typmsg] == "undefined"){
                            amodifsdestinataires[idV][typmsg] = new Object();
                        }

                        if (typeof amodifsdestinataires[idV][typmsg][idaltea] == "undefined"){
                            amodifsdestinataires[idV][typmsg][idaltea] = new Object();
                        }


                        if (typeof amodifsdestinataires[idV][typmsg][idaltea]["aAdressesSITAaAjouter"] == "undefined"){
                            amodifsdestinataires[idV][typmsg][idaltea]["aAdressesSITAaAjouter"] = new Array();
                        }

                        if(addrsita==""){
                            addrsita = " ";
                        }
                        if(emailsita==""){
                            emailsita = " ";
                        }
                        if(libellesita==""){
                            libellesita = " ";
                        }
                        if(groupesita==""){
                            groupesita = " ";
                        }
                        if(coordinateursita=="-"){
                            coordinateursita = " ";
                        }

                        amodifsdestinataires[idV][typmsg][idaltea]["aAdressesSITAaAjouter"].push(addrsita+"#"+emailsita+"#"+libellesita+"#"+groupesita+"#"+coordinateursita);

                    }

                }

            }

        });

        //alert(JSON.stringify(amodifsdestinataires, null, 2));
        // on fait confirmer l'envoi des messages par  l'utilisateur
        // ----------------------------------------------------------
        bootbox.confirm({
            message: 'Attention, êtes-vous sûr de vouloir expédier les messages des vols selectionnés ?',
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
                if (result) {// OUI selectionné

                    var url = Routing.generate('vol_expedier');

                    //on modifie la variable de session du templatecourant
                    $.ajax({
                        url: url, /*'http://'+window.location.hostname+$('#ajaxmethodsurls').attr('data-setparametresplanningvol-route'),*/
                        dataType: 'json',
                        type: 'POST',
                        data: {
                            idsVolCheckedString: idsVolCheckedString,
                            idsVolNotCheckedString: idsVolNotCheckedString,
                            amodifsdestinataires: amodifsdestinataires
                        },
                        success: function(doc) {
                            //alert("success!! ");
                            //bootbox.alert("<i class='glyphicon glyphicon-info-sign' style='color: royalblue;'></i> L\'expédition a été effectué.");

                            //redirection sur la page précédentes avec les mêmes filtres choisis
                            var temp = $("#paramsListeVolsFilter").val().replace("%5B%5D", "[]");
                            var tempParams = temp.split("&");
                            var params = [];
                            tempParams.forEach(function(currentValue, index, arr){
                                var tempVars = currentValue.split("=");
                                if(tempVars[0]=="jours%5B%5D"){tempVars[0] = "jours[]"+tempVars[1]} //correction du bug Patrice les différents jour ont le même nom de variables
                                params[tempVars[0]] = tempVars[1];
                            });

                            window.location.href = Routing.generate('vol_liste',params);

                        },
                        error: function(){
                            //alert("error!! ");
                            bootbox.alert("<i class='glyphicon glyphicon-alert' style='color: red;'></i> Erreur d\'expédition.");
                        }
                    });

                } else {// NON selectionné

                }
            }
        });


    });

});
