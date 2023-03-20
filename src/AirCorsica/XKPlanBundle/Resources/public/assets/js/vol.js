$(function(){

    $('#rec').click(function(){
        $('#rec').focus();

        //recherche d'incphérence de saisie de période de vol et d'incohérence d'heure dans le cas d'une période de vol d'un jour
        var debutperiodestring = $("#aircorsica_xkplanbundle_vol_periodeDeVol_dateDebut").val();
        var debutperiodemoment = moment(debutperiodestring, 'DD-MM-YYYY');
        var finperiodestring = $("#aircorsica_xkplanbundle_vol_periodeDeVol_dateFin").val();
        var finperiodemoment = moment(finperiodestring, 'DD-MM-YYYY');
        var samedaydecollagemoment = moment(debutperiodestring+" "+$("#aircorsica_xkplanbundle_vol_periodeDeVol_decollage").val()+":00", 'DD-MM-YYYY hh:mm:ss');
        var samedayatterissagemoment = moment(debutperiodestring+" "+$("#aircorsica_xkplanbundle_vol_periodeDeVol_atterissage").val()+":00", 'DD-MM-YYYY hh:mm:ss');

        if( debutperiodemoment.isSame(finperiodemoment)){//vol définit sur une période de 1 jour
            if( samedayatterissagemoment.isBefore(samedaydecollagemoment) ) {//sinon si l'heure de décollage est aprés l'heure d'attérissagee
                $("#aircorsica_xkplanbundle_vol_periodeDeVol_dateFin").css('color','black');//remise à zero de la couleur du champ non problématique
                $("#aircorsica_xkplanbundle_vol_periodeDeVol_atterissage").css('color','red');//on colorie l'erreur
                $("#aircorsica_xkplanbundle_vol_periodeDeVol_atterissage").focus();//on positionne le curseur sur l'erreur
                alert('Attention: l\'heure d\atterissage précéde l\heure de décollage!');
                return false;
            }
        } else if( finperiodemoment.isBefore(debutperiodemoment) ){//sinon si le début de période est aprés la fin de période
            $("#aircorsica_xkplanbundle_vol_periodeDeVol_atterissage").css('color','black');//remise à zero de la couleur du champ non problématique
            $("#aircorsica_xkplanbundle_vol_periodeDeVol_dateFin").css('color','red');//on colorie l'erreur
            $("#aircorsica_xkplanbundle_vol_periodeDeVol_dateFin").focus();//on positionne le curseur sur l'erreur
            alert('Attention: la date de fin de période de validité précéde la date de début de période de validité!');
            return false;
        }

        if($('#aircorsica_xkplanbundle_vol_numero').val().length<5){
            $('#aircorsica_xkplanbundle_vol_numero').css('color','red');
            $('#aircorsica_xkplanbundle_vol_numero').focus();
            return false;
        }
        if ($('.checkbox-jour-semaine:checked').length > 0) {
            $('#form_vol_edit').submit();
        }else{
            alert("Veuillez sélectionner au moins un jour de validité !");
        }
    })
    $('#bt-poursuivre').click(function(){
        $('#bt-poursuivre').focus();

        //recherche d'incphérence de saisie de période de vol et d'incohérence d'heure dans le cas d'une période de vol d'un jour
        var debutperiodestring = $("#aircorsica_xkplanbundle_vol_periodeDeVol_dateDebut").val();
        var debutperiodemoment = moment(debutperiodestring, 'DD-MM-YYYY');
        var finperiodestring = $("#aircorsica_xkplanbundle_vol_periodeDeVol_dateFin").val();
        var finperiodemoment = moment(finperiodestring, 'DD-MM-YYYY');
        var samedaydecollagemoment = moment(debutperiodestring+" "+$("#aircorsica_xkplanbundle_vol_periodeDeVol_decollage").val()+":00", 'DD-MM-YYYY hh:mm:ss');
        var samedayatterissagemoment = moment(debutperiodestring+" "+$("#aircorsica_xkplanbundle_vol_periodeDeVol_atterissage").val()+":00", 'DD-MM-YYYY hh:mm:ss');

        if( debutperiodemoment.isSame(finperiodemoment)){//vol définit sur une période de 1 jour
            if( samedayatterissagemoment.isBefore(samedaydecollagemoment) ) {//sinon si l'heure de décollage est aprés l'heure d'attérissagee
                $("#aircorsica_xkplanbundle_vol_periodeDeVol_dateFin").css('color','black');//remise à zero de la couleur du champ non problématique
                $("#aircorsica_xkplanbundle_vol_periodeDeVol_atterissage").css('color','red');//on colorie l'erreur
                $("#aircorsica_xkplanbundle_vol_periodeDeVol_atterissage").focus();//on positionne le curseur sur l'erreur
                alert('Attention: l\'heure d\atterissage précéde l\heure de décollage!');
                return false;
            }
        } else if( finperiodemoment.isBefore(debutperiodemoment) ){//sinon si le début de période est aprés la fin de période
            $("#aircorsica_xkplanbundle_vol_periodeDeVol_atterissage").css('color','black');//remise à zero de la couleur du champ non problématique
            $("#aircorsica_xkplanbundle_vol_periodeDeVol_dateFin").css('color','red');//on colorie l'erreur
            $("#aircorsica_xkplanbundle_vol_periodeDeVol_dateFin").focus();//on positionne le curseur sur l'erreur
            alert('Attention: la date de fin de période de validité précéde la date de début de période de validité!');
            return false;
        }

        if($('#aircorsica_xkplanbundle_vol_numero').val().length<5){
            $('#aircorsica_xkplanbundle_vol_numero').css('color','red');
            $('#aircorsica_xkplanbundle_vol_numero').focus();
            return false;
        }
        if ($('.checkbox-jour-semaine:checked').length > 0) {
            $('#form_vol_edit').submit();
        }else{
            alert("Veuillez sélectionner au moins un jour de validité !");
        }
    })

    if($('#id_precedent').val() != ""){
        $('#aircorsica_xkplanbundle_vol_codesShareVol').empty();
    }

    setValues(null,1);
    $('#load_code_share').click(function(){
        loadCodeSharePrecharges();
    });

    if($('#aircorsica_xkplanbundle_vol_numero').val()){
        if($('form').attr('id_precedent') == undefined || $('form').attr('id_precedent').length == 0) {
            loadCodeShare();
        }
    }

    $('#aircorsica_xkplanbundle_vol_numero').on('input',function(){
        if($(this).val().length !== 5 ){
            return;
        }

        var $typeVol = $('#aircorsica_xkplanbundle_vol_typeDeVol');
        var val = $(this).val().substr(2);
        if( (val >= 1 && val <=499) || (val >= 700 && val <=799)){
            $typeVol.val(1).trigger('change');
            return;
        }

        if( val >= 500 && val <=699){
            $typeVol.val(2).trigger('change');
            return;
        }

        if( val >= 800 && val <=899){
            $typeVol.val(3).trigger('change');
            return;
        }

        if( val >= 900 && val <=919){
            $typeVol.val(4).trigger('change');
            return;
        }

        if( val >= 920 && val <=949){
            $typeVol.val(5).trigger('change');
            return;
        }

        if( val >= 950 && val <=979){
            $typeVol.val(6).trigger('change');
            return;
        }

    });

    $('#aircorsica_xkplanbundle_vol_periode2').click(function(){
        if($(this).prop('checked') == true){
            $('.periode2').show();
            $('#aircorsica_xkplanbundle_vol_periodeDeVol2_dateDebut').val($('#aircorsica_xkplanbundle_vol_periodeDeVol_dateDebut').val());
            $('#aircorsica_xkplanbundle_vol_periodeDeVol2_dateFin').val($('#aircorsica_xkplanbundle_vol_periodeDeVol_dateFin').val());

        }else{
            $('.periode2').hide();
            $('#aircorsica_xkplanbundle_vol_periodeDeVol2_dateDebut').val('');
            $('#aircorsica_xkplanbundle_vol_periodeDeVol2_dateFin').val('');
        }
    });


    $('#aircorsica_xkplanbundle_vol_avion,#aircorsica_xkplanbundle_vol_periodeDeVol_decollage,#aircorsica_xkplanbundle_vol_periodeDeVol_dateDebut,#aircorsica_xkplanbundle_vol_periodeDeVol_dateFin').blur(function() {
        setValues($(this));
    });

    ////validation
    //$("#rec").attr('type','text');
    //$("#rec").on("click",function(e){
    //
    //    var erreursDeValidation = 0;
    //
    //    //verification selection d'un avion
    //    if($("#aircorsica_xkplanbundle_vol_avion").val() == ""){
    //        erreursDeValidation++;
    //    }
    //
    //    //verification période de validité 1
    //    if( ($("#aircorsica_xkplanbundle_vol_periodeDeVol_dateDebut").val() != "") && ($("#aircorsica_xkplanbundle_vol_periodeDeVol_dateFin").val() != "") ) {
    //        var periode1validedatedebut = moment($("#aircorsica_xkplanbundle_vol_periodeDeVol_dateDebut").val(), "DD-MM-YYYY");
    //        var periode1validedatefin = moment($("#aircorsica_xkplanbundle_vol_periodeDeVol_dateFin").val(), "DD-MM-YYYY");
    //        if (periode1validedatefin.isBefore(periode1validedatedebut, 'day')) {
    //            erreursDeValidation++;
    //        }
    //    }else{
    //        erreursDeValidation++;
    //    }
    //
    //    //verification période de validité 2
    //    if($(".periode2").css('display') != 'none'){
    //
    //        if( ($("#aircorsica_xkplanbundle_vol_periodeDeVol2_dateDebut").val() != "") && ($("#aircorsica_xkplanbundle_vol_periodeDeVol2_dateFin").val() != "") ) {
    //            var periode2validedatedebut = moment($("#aircorsica_xkplanbundle_vol_periodeDeVol2_dateDebut").val(), "DD-MM-YYYY");
    //            var periode2validedatefin = moment($("#aircorsica_xkplanbundle_vol_periodeDeVol2_dateFin").val(), "DD-MM-YYYY");
    //            if( periode2validedatefin.isBefore(periode2validedatedebut, 'day') ){
    //                erreursDeValidation++;
    //            }
    //        }else{
    //            erreursDeValidation++;
    //        }
    //
    //    }
    //
    //    //verification du décollage/atterissage
    //    var decollage = $("#aircorsica_xkplanbundle_vol_periodeDeVol_decollage").val();
    //    intdecollage = parseInt(decollage.replace(":",""));
    //    var atterissage = $("#aircorsica_xkplanbundle_vol_periodeDeVol_atterissage").val();
    //    intatterissage = parseInt(atterissage.replace(":",""));
    //
    //    if(atterissage<=decollage){
    //        erreursDeValidation++;
    //    }
    //
    //    //verification jours de validités
    //    var nbjourschecked = 0;
    //    $(".checkbox-jour-semaine ").each(function(){
    //        if ($(this).is(':checked')){
    //            nbjourschecked++;
    //        }
    //    });
    //    if(nbjourschecked == 0){
    //        erreursDeValidation++;
    //    }
    //
    //    $("#rec").attr('type','submit');
    //    $("#rec").trigger("click");
    //});



});

function setValues(elm,test){
    $('#rec').addClass('not-active');
    if(('#bt-poursuivre').length){
        $('#bt-poursuivre').addClass('not-active');
    }

    if($('#aircorsica_xkplanbundle_vol_periode2').prop('checked') == true){
        $('.periode2').show();
    }

    if($('#aircorsica_xkplanbundle_vol_dates2_precedent').val() != "_"){
        var dates2 = $('#aircorsica_xkplanbundle_vol_dates2_precedent').val().split('_');
        var dateDebut2 = dates2[0];
        var dateFin2 = dates2[1];
        $('#aircorsica_xkplanbundle_vol_periodeDeVol2_dateDebut').val(dateDebut2);
        $('#aircorsica_xkplanbundle_vol_periodeDeVol2_dateFin').val(dateFin2);
    }

    var $avion = $('#aircorsica_xkplanbundle_vol_avion');
    $('#aircorsica_xkplanbundle_vol_temps_demi_tour').val($avion.find('option:selected').data('demitour-value'));

    if($('#aircorsica_xkplanbundle_vol_id').val() == "") {
        $('#aircorsica_xkplanbundle_vol_compagnie').val($avion.find('option:selected').data('compagnie')).trigger('change');
    }

    if(
        $('#aircorsica_xkplanbundle_vol_aeroport_depart').val() &&
        $('#aircorsica_xkplanbundle_vol_aeroport_arrivee').val() &&
        $('#aircorsica_xkplanbundle_vol_periodeDeVol_dateDebut').val() &&
        $('#aircorsica_xkplanbundle_vol_periodeDeVol_dateFin').val()
    ) {

        var dateDebut = $('#aircorsica_xkplanbundle_vol_periodeDeVol_dateDebut').val();
        var dateFin = $('#aircorsica_xkplanbundle_vol_periodeDeVol_dateFin').val();
        var aeroport_depart = $('#aircorsica_xkplanbundle_vol_aeroport_depart').val();
        var aeroport_arrivee = $('#aircorsica_xkplanbundle_vol_aeroport_arrivee').val();

        var avion = $('#aircorsica_xkplanbundle_vol_avion').val();

        $.ajax({
            type: 'get',
            url: Routing.generate('vol_gettdv', {
                aeroport_depart: aeroport_depart,
                aeroport_arrivee: aeroport_arrivee,
                avion: avion,
                dateDebut: dateDebut,
                dateFin: dateFin
            }),
            success: function (data) {

                // if ('null' != data.duree) {
                var hours = Math.trunc(data.duree / 60);
                var minutes = data.duree % 60;

                if(data.duree == null){
                    data.duree = "59";
                }

                $('#infos-tdv').text(hours + 'heure(s) et ' + minutes + 'minute(s)');


                var heure_decollage = $('#aircorsica_xkplanbundle_vol_periodeDeVol_decollage').val();
                if (heure_decollage != "") {
                    var heure_atterissage = $('#aircorsica_xkplanbundle_vol_periodeDeVol_atterissage');
                    var decollage = moment(heure_decollage, "HH:mm");

                    // if($(elm).attr('id') == "aircorsica_xkplanbundle_vol_periodeDeVol_decollage" || ($('form').attr('id_precedent') != "" && $('form').attr('id_precedent') != undefined))
                    // {
                if(!test || ($('form').attr('id_precedent') != "" && $('form').attr('id_precedent') != undefined)){
                    var atterissage = decollage.add(data.duree, 'm');
                    heure_atterissage.val(atterissage.format("HH:mm"));
                }

                    // }

                    // if ($('.checkbox-jour-semaine:checked').length > 0) {
                        $('#rec').removeClass('not-active');
                        if (('#bt-poursuivre').length) {
                            $('#bt-poursuivre').removeClass('not-active');
                        }
                    // }
                }
            }
        });
    }
}

function loadCodeShare(){
    if($('#aircorsica_xkplanbundle_vol_id').val()){
        var id = $('#aircorsica_xkplanbundle_vol_id').val();
    }else if($('#aircorsica_xkplanbundle_vol_id_precedent').val()) {
        var id = $('#aircorsica_xkplanbundle_vol_id_precedent').val();
    }

    if(id){
        $.ajax({
            type: 'get',
            url: Routing.generate('vol_getcodessharevol', { id : id}),
            success: function (data) {
                $("#aircorsica_xkplanbundle_vol_codesShareVol").empty();
                $.each(data, function(index, cs){
                    var newOption = new Option(cs.libelle, cs.libelle, true, true);
                    $("#aircorsica_xkplanbundle_vol_codesShareVol").append(newOption).trigger('change');
                });
            }
        });
    }
}

function loadCodeSharePrecharges(){
    var libelle = $('#aircorsica_xkplanbundle_vol_numero').val();
    if( libelle.length < 3){
        return
    }
    $("#aircorsica_xkplanbundle_vol_codesShareVol").empty();
    $.ajax({
        type: 'get',
        url: Routing.generate('vol_getcodessharesprecharges', { libelle:  libelle}),
        success: function (data) {

            $.each(data, function(index, cs){
                var newOption = new Option(cs.libelle, cs.libelle, true, true);
                $("#aircorsica_xkplanbundle_vol_codesShareVol").append(newOption).trigger('change');
            });
        }
    });
}

