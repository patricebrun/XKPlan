$(function () {
   $('#b_init').click(function(e){
       if($('fieldset:first').find('span.bck-red').length){
           messageErreurForm();
           return false;
       }
       e.preventDefault();
       bootbox.confirm({
           message: 'Attention vous avez demandé à initialiser les valeurs des temps de vols pour le type d\'avion' +
                    ' en cours. Tous les temps de vols déjà définis pour ce type d\'avion et les options choisies(ligne,...)' +
                    ' vont être remplacé par cette nouvelle valeur. <br><br> Cette opération est irréversible : désirez-vous continuer ?',

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
                   var url = Routing.generate('tempsdevol_init');
                   $('#form_temps_de_vol').attr('action',url);
                   $('#form_temps_de_vol').submit();
               }
           }
       });
   });

    $('#d_del_all').click(function(e){
        var id = $(this).data('id');
        e.preventDefault();
        bootbox.confirm({
            message: 'Attention vous allez effacer toutes les valeurs de temps de vol définies pour' +
            ' ce type d\'avion. <br><br> Cette opération est irréversible : désirez-vous continuer ?',

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
                    var url = Routing.generate('typeavion_deleteAll',{'id' : id});
                    $('#form_temps_de_vol').attr('action',url);
                    $('#form_temps_de_vol').submit();
                }
            }
        });
    });

    $('#b_copie').click(function(e){
        if($('fieldset:last').find('span.bck-red').length){
            messageErreurForm();
            return false;
        }
        var id = $(this).data('id');
        var sensRetour = "NON";
        if($('#retour_duplication').is(':checked')){
            sensRetour = "OUI";
        }

        e.preventDefault();
        bootbox.confirm({
            message: 'Vous avez demandé à dupliquer les temps de vols de ce type d\'avion selon les paramètres suivants : ' +
                     '<br><br> TYPE AVION > <strong>'+ $('#aircorsica_xkplanbundle_typeavion_version').val()+ '</strong>'+
                     '<br> Code IATA > <strong>'+ $('#aircorsica_xkplanbundle_typeavion_codeIATA').val()+ '</strong>'+
                     '<br><br><br> AEROPORT DEPART > <strong>' +  $('#aeroportDepartSource option[value='+$('#aeroportDepartSource').val()+']').text() + '</strong>'+
                     '<br> AEROPORT ARRIVEE > <strong>' + $('#aeroportDepartCible option[value='+$('#aeroportDepartCible').val()+']').text() + '</strong>'+
                     '<br><br> SENS RETOUR > <strong>' + sensRetour + '</strong>'+
                     '<br><br> PERIODE SAISON SOURCE > <strong>' + $('#periode_saison_source option[value='+$('#periode_saison_source').val()+']').text() + '</strong>'+
                     '<br> PERIODE SAISON CIBLE > <strong>' + $('#periode_saison_cible option[value='+$('#periode_saison_cible').val()+']').text() + '</strong>'+
                     '<br><br> Si des données existent pour les paramères CIBLE, ceux-ci seront au préalable effacés.'+
                     '<br><br>Cete opération est irréversible. Désirez-vous coninuer ? ',

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
                    var url = Routing.generate('typeavion_copie',{'id' : id});
                    $('#form_temps_de_vol').attr('action',url);
                    $('#form_temps_de_vol').submit();
                }
            }
        });



    });

    $('.btn-submit-tdv').click(function (e) {
       var id = $(this).data('id');
       var url = Routing.generate('tempsdevol_edit',{'id' : id});
       $("#form-update-tdv").attr('action',url);
       $("#form-update-tdv-heure").val(
           $('#heure-'+id).val()
       );

        $("#form-update-tdv-minute").val(
            $('#minute-'+id).val()
        );

        $("#form-update-tdv").submit();
    });


    //Filtre de Recherche Saison et ligne des temps de vol
    // Setup - add a text input to each footer cell
    $('#datatable tfoot th').each( function () {
        var title = $(this).text();
        if (title != "") {
            $(this).html('<input type="text" placeholder="Rech. ' + title + '" style="width: 100%;padding: 3px;box-sizing: border-box;"/>');
        }else{
            $(this).html('<div>-</div>');
        }
    } );

    // DataTable
    var table = $('#datatable').DataTable();

    // Apply the search
    table.columns().every( function () {
        var that = this;

        $( 'input', this.footer() ).on( 'keyup change', function () {
            if ( that.search() !== this.value ) {
                that
                    .search( this.value )
                    .draw();
            }
        } );
    } );

});