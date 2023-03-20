'use strict';
//  Author: TemplateMonster.com
//
//  This file is reserved for changes made by the use.
//  Always seperate your work from the theme. It makes
//  modifications, and future theme updates much easier
//
(function($) {


    $('#timeTableRafraichir').click(function () {
        if($('form').find('span.bck-red').length){
            messageErreurForm();
            return false;
        }
        $('#myModal').modal('toggle');
        $('#myModal .modal-body').html("<span>Veuillez patienter pendant la génération du TimeTable. Cela peut pendre quelques minutes.</span><div class='progress' id='loadingDiv'><div class='progress-bar progress-bar-striped progress-bar-success active' role='progressbar' \n\
                                 aria-valuenow='100' aria-valuemin='0' aria-valuemax='100' style='width: 100%'></div></div>");
    })


    // Place custom scripts here
    //------------- Data tables -------------//
    //responsive datatables
    var table = $('.datatable').dataTable( {
        bom: true,
        /*dom:    "<'row'<'col-md-4'l><'col-md-4'B><'col-md-4'f>>" +
        "<'row'<'col-md-12'tr>>" +
        "<'row'<'col-md-5'i><'col-md-7'p>>",*/

        dom: '<"dt-panelmenu clearfix"lBfr>t<"dt-panelfooter clearfix"ip>',
        buttons: [
            {
                extend: 'csvHtml5',
                action: function ( e, dt, node, config ) {
                    var res = parseQueryString();
                    var param = "";
                    $.each( res, function( key, value ) {
                        param += key+"="+value+"&";
                    });
                    bootbox.confirm({
                        message: 'Si vous confirmez cette action, veuillez patienter pendant la génération du TimeTable au format CSV. Le fichier sera automatiquement téléchargé sur votre ordinatuer.',
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
                            if(result){
                                window.location.href = Routing.generate('vol_timetabletocsv')+"?"+param.substr(0,param.length-1);
                            }
                        }
                    });
                }
            },
            {
                extend: 'pdfHtml5',
                action: function ( e, dt, node, config ) {
                    var res = parseQueryString();
                    var param = "";
                    $.each( res, function( key, value ) {
                        param += key+"="+value+"&";
                    });
                    bootbox.confirm({
                        message: 'Si vous confirmez cette action, veuillez patienter pendant la génération du TimeTable au format PDF. Le fichier sera automatiquement ouvert dans votre navigateur et vous pourrez le télécharger ou bien l\'imprimer.',
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
                            if(result){
                                window.open(Routing.generate('vol_timetabletopdf')+"?"+param.substr(0,param.length-1));
                            }
                        }
                    });
                }
            },
        ],
        "drawCallback": function ( settings ) {
            var api = this.api();
            var rows = api.rows( {page:'current'} ).nodes();
            var last=null;
            // var colonne = api.row(0).data().length;
            // var totale = new Array();
            // totale['Totale']= new Array();
            var groupid = -1;
            // var subtotale = new Array();

            api.column(1, {page:'current'} ).data().each( function ( group, i ) {
                if ( last !== group ) {
                    groupid++;
                    $(rows).eq( i ).before(
                        '<tr class="header"><td colspan="10">'+group+'</td></tr>'
                    );

                    last = group;
                }

                // var val = api.row(api.row($(rows).eq( i )).index()).data(); //current order index
                //
                // $.each(val,function(index2,val2){
                //     if (typeof subtotale[groupid] =='undefined'){
                //         subtotale[groupid] = new Array();
                //     }
                //     if (typeof subtotale[groupid][index2] =='undefined'){
                //         subtotale[groupid][index2] = 0;
                //     }
                //     if (typeof totale['Totale'][index2] =='undefined'){ totale['Totale'][index2] = 0; }
                //
                //     //var valore = Number(val2.replace('€',"").replace('.',"").replace(',',"."));
                //     var valore = Number(val2);
                //     subtotale[groupid][index2] += valore;
                //     totale['Totale'][index2] += valore;
                // });
            });

            // $('tbody').find('.group').each(function (i,v) {
            //     var rowCount = $(this).nextUntil('.group').length;
            //     $(this).find('td:first').append($('<span />', { 'class': 'rowCount-grid' }).append($('<b />', { 'text': ' ('+rowCount+')' })));
            //     var subtd = '';
            //     for (var a=3;a<colonne;a++)
            //     {
            //         console.log(Math.round(subtotale[i][a]*100)/100);
            //         subtd += '<td>'+Math.round(subtotale[i][a]*100)/100+'</td>';
            //     }
            //     $(this).append(subtd);
            // });
            //
            // for (var foot=3;foot<6;foot++)
            // {
            //     $( api.column(foot).footer() ).html(
            //         Math.round(api.column( foot, {page:'current'} ).data().sum() *100)/100
            //     );
            // }
        },
        fieldSeparator : ";",
        /*"lengthMenu": [ [10, 25, 50, 100, -1], [10, 25, 50, 100, "Tout"] ],*/
        paging: false,
        language: {
            "sProcessing":     "Traitement en cours...",
            "sSearch":         "Rechercher&nbsp;:",
            "sLengthMenu":     "Afficher _MENU_ &eacute;l&eacute;ments",
            "sInfo":           "Affichage de l'&eacute;l&eacute;ment _START_ &agrave; _END_ sur _TOTAL_ &eacute;l&eacute;ments",
            "sInfoEmpty":      "Affichage de l'&eacute;l&eacute;ment 0 &agrave; 0 sur 0 &eacute;l&eacute;ment",
            "sInfoFiltered":   "(filtr&eacute; de _MAX_ &eacute;l&eacute;ments au total)",
            "sInfoPostFix":    "",
            "sLoadingRecords": "Chargement en cours...",
            "sZeroRecords":    "Aucun &eacute;l&eacute;ment &agrave; afficher",
            "sEmptyTable":     "Aucune donn&eacute;e disponible dans le tableau"
            // "oPaginate": {
            //     "sFirst":      "Premier",
            //     "sPrevious":   "Pr&eacute;c&eacute;dent",
            //     "sNext":       "Suivant",
            //     "sLast":       "Dernier"
            // },
            // "oAria": {
            //     "sSortAscending":  ": activer pour trier la colonne par ordre croissant",
            //     "sSortDescending": ": activer pour trier la colonne par ordre d&eacute;croissant"
            // }
        },
        "order": [],
        "columnDefs": [ {
            "targets": 'no-sort',
            "orderable": false
        } ],
        // "drawCallback": function ( settings ) {
        //     var api = this.api();
        //     var rows = api.rows( {page:'current'} ).nodes();
        //     var last=null;
        //
        //     api.column(1, {page:'current'} ).data().each( function ( group, i ) {
        //         if ( last !== group ) {
        //             $(rows).eq( i ).before(
        //                 '<tr class="group"><td colspan="9">'+group+'</td></tr>'
        //             );
        //
        //             last = group;
        //         }
        //     } );
        // }
    } );

    // // Order by the grouping
    // $('#datatable1 tbody').on( 'click', 'tr.group', function () {
    //     var currentOrder = table.order()[0];
    //     if ( currentOrder[0] === 2 && currentOrder[1] === 'asc' ) {
    //         table.order( [ 2, 'desc' ] ).draw();
    //     }
    //     else {
    //         table.order( [ 2, 'asc' ] ).draw();
    //     }
    // } );

})(jQuery);

function getParameterByName( name ){
    var regexS = "[\\?&]"+name+"=([^&#]*)",
        regex = new RegExp( regexS ),
        results = regex.exec( window.location.search );
    if( results == null ){
        return "";
    } else{
        return decodeURIComponent(results[1].replace(/\+/g, " "));
    }
}

function parseQueryString () {
    var parsedParameters = {},
        uriParameters = location.search.substr(1).split('&');

    for (var i = 0; i < uriParameters.length; i++) {
        var parameter = uriParameters[i].split('=');
        parsedParameters[parameter[0]] = decodeURIComponent(parameter[1]);
    }

    return parsedParameters;
}
