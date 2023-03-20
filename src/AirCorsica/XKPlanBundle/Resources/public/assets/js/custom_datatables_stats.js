'use strict';
//  Author: TemplateMonster.com
//
//  This file is reserved for changes made by the use.
//  Always seperate your work from the theme. It makes
//  modifications, and future theme updates much easier
//

jQuery.fn.dataTable.Api.register( 'sum()', function ( ) {
    return this.flatten().reduce( function ( a, b ) {
        if ( typeof a === 'string' ) {
            a = a.replace(/[^\d.-]/g, '') * 1;
        }
        if ( typeof b === 'string' ) {
            b = b.replace(/[^\d.-]/g, '') * 1;
        }

        return a + b;
    }, 0 );
} );

(function($) {
    // Place custom scripts here
    //------------- Data tables -------------//
    //responsive datatables
    $('#datatable1').dataTable( {
        "order": false,
        /*dom:    "<'row'<'col-md-4'l><'col-md-4'B><'col-md-4'f>>" +
         "<'row'<'col-md-12'tr>>" +
         "<'row'<'col-md-5'i><'col-md-7'p>>",*/
        dom: '<"dt-panelmenu clearfix"lBfr>t<"dt-panelfooter clearfix"ip>',
        buttons: [
            'excelHtml5',
            {
                extend: 'pdfHtml5',
                action: function ( e, dt, node, config ) {
                    var res = parseQueryString();
                    var param = "";
                    $.each( res, function( key, value ) {
                        if(value.indexOf("#") !== -1){
                            var tab = value.split("#");
                            for(var i=0; i < tab.length ; i++){
                                param += key+"="+tab[i]+"&";
                            }
                        }else{
                            param += key+"="+value+"&";
                        }
                    });
                    window.open(Routing.generate('statistiques_export_pdf_ligne')+"?"+param.substr(0,param.length-1));
                }
            }
        ],

        /*"lengthMenu": [ [10, 25, 50, 100, -1], [10, 25, 50, 100, "Tout"] ],*/
        paging: false,
        language: {
            "sProcessing":     "Traitement en cours...",
            "sSearch":         "Rechercher&nbsp;:",
            "lengthMenu":      "Afficher _MENU_ &eacute;l&eacute;ments",
            //"sLengthMenu":     "Afficher _MENU_ &eacute;l&eacute;ments",
            "sInfo":           "Affichage de l'&eacute;l&eacute;ment _START_ &agrave; _END_ sur _TOTAL_ &eacute;l&eacute;ments",
            "sInfoEmpty":      "Affichage de l'&eacute;l&eacute;ment 0 &agrave; 0 sur 0 &eacute;l&eacute;ment",
            "sInfoFiltered":   "(filtr&eacute; de _MAX_ &eacute;l&eacute;ments au total)",
            "sInfoPostFix":    "",
            "sLoadingRecords": "Chargement en cours...",
            "sZeroRecords":    "Aucun &eacute;l&eacute;ment &agrave; afficher",
            "sEmptyTable":     "Aucune donn&eacute;e disponible dans le tableau",
            "oPaginate": {
                "sFirst":      "Premier",
                "sPrevious":   "Pr&eacute;c&eacute;dent",
                "sNext":       "Suivant",
                "sLast":       "Dernier"
            },
            // "oAria": {
            //     "sSortAscending":  ": activer pour trier la colonne par ordre croissant",
            //     "sSortDescending": ": activer pour trier la colonne par ordre d&eacute;croissant"
            // }
        }
    } );






    $('#datatable2').dataTable( {
        "order": false,
        dom: '<"dt-panelmenu clearfix"lBfr>t<"dt-panelfooter clearfix"ip>',
        buttons: [
            'excelHtml5',
            {
                extend: 'pdfHtml5',
                action: function ( e, dt, node, config ) {
                    var res = parseQueryString();
                    var param = "";
                    $.each( res, function( key, value ) {
                        if(value.indexOf("#") !== -1){
                            var tab = value.split("#");
                            for(var i=0; i < tab.length ; i++){
                                param += key+"="+tab[i]+"&";
                            }
                        }else{
                            param += key+"="+value+"&";
                        }
                    });
                    window.open(Routing.generate('statistiques_export_pdf_avion')+"?"+param.substr(0,param.length-1));
                }
            }
        ],
        paging: false,
        language: {
            "sProcessing":     "Traitement en cours...",
            "sSearch":         "Rechercher&nbsp;:",
            "lengthMenu":      "Afficher _MENU_ &eacute;l&eacute;ments",
            //"sLengthMenu":     "Afficher _MENU_ &eacute;l&eacute;ments",
            "sInfo":           "Affichage de l'&eacute;l&eacute;ment _START_ &agrave; _END_ sur _TOTAL_ &eacute;l&eacute;ments",
            "sInfoEmpty":      "Affichage de l'&eacute;l&eacute;ment 0 &agrave; 0 sur 0 &eacute;l&eacute;ment",
            "sInfoFiltered":   "(filtr&eacute; de _MAX_ &eacute;l&eacute;ments au total)",
            "sInfoPostFix":    "",
            "sLoadingRecords": "Chargement en cours...",
            "sZeroRecords":    "Aucun &eacute;l&eacute;ment &agrave; afficher",
            "sEmptyTable":     "Aucune donn&eacute;e disponible dans le tableau",
            "oPaginate": {
                "sFirst":      "Premier",
                "sPrevious":   "Pr&eacute;c&eacute;dent",
                "sNext":       "Suivant",
                "sLast":       "Dernier"
            },
            "oAria": {
                "sSortAscending":  ": activer pour trier la colonne par ordre croissant",
                "sSortDescending": ": activer pour trier la colonne par ordre d&eacute;croissant"
            }
        }
    } );



    $('#datatable3').dataTable( {
        "order": [[ 0, "asc" ]],
        /*dom:    "<'row'<'col-md-4'l><'col-md-4'B><'col-md-4'f>>" +
        "<'row'<'col-md-12'tr>>" +
        "<'row'<'col-md-5'i><'col-md-7'p>>",*/
        dom: '<"dt-panelmenu clearfix"lBfr>t<"dt-panelfooter clearfix"ip>',
        buttons: [

            'excelHtml5',
            {
                extend: 'pdfHtml5',
                action: function ( e, dt, node, config ) {
                    var res = parseQueryString();
                    var param = "";
                    $.each( res, function( key, value ) {
                        if(value.indexOf("#") !== -1){
                            var tab = value.split("#");
                            for(var i=0; i < tab.length ; i++){
                                param += key+"="+tab[i]+"&";
                            }
                        }else{
                            param += key+"="+value+"&";
                        }
                    });
                    // alert(Routing.generate('statistiques_export_pdf_generaux')+"?"+param.substr(0,param.length-1));
                    window.open(Routing.generate('statistiques_export_pdf_generaux')+"?"+param.substr(0,param.length-1));
                }
            },

        ],
        // "drawCallback": function ( settings ) {
        //     var api = this.api();
        //
        //     for (var foot=1;foot<4;foot++)
        //     {
        //         $( api.column(foot).footer() ).html(
        //             Math.round(api.column( foot, {page:'current'} ).data().sum() *100)/100
        //         );
        //     }
        // },
        /*"lengthMenu": [ [10, 25, 50, 100, -1], [10, 25, 50, 100, "Tout"] ],*/
        paging: false,
        language: {
            "sProcessing":     "Traitement en cours...",
            "sSearch":         "Rechercher&nbsp;:",
            "lengthMenu":      "Afficher _MENU_ &eacute;l&eacute;ments",
            //"sLengthMenu":     "Afficher _MENU_ &eacute;l&eacute;ments",
            "sInfo":           "Affichage de l'&eacute;l&eacute;ment _START_ &agrave; _END_ sur _TOTAL_ &eacute;l&eacute;ments",
            "sInfoEmpty":      "Affichage de l'&eacute;l&eacute;ment 0 &agrave; 0 sur 0 &eacute;l&eacute;ment",
            "sInfoFiltered":   "(filtr&eacute; de _MAX_ &eacute;l&eacute;ments au total)",
            "sInfoPostFix":    "",
            "sLoadingRecords": "Chargement en cours...",
            "sZeroRecords":    "Aucun &eacute;l&eacute;ment &agrave; afficher",
            "sEmptyTable":     "Aucune donn&eacute;e disponible dans le tableau",
            "oPaginate": {
                "sFirst":      "Premier",
                "sPrevious":   "Pr&eacute;c&eacute;dent",
                "sNext":       "Suivant",
                "sLast":       "Dernier"
            },
            "oAria": {
                "sSortAscending":  ": activer pour trier la colonne par ordre croissant",
                "sSortDescending": ": activer pour trier la colonne par ordre d&eacute;croissant"
            }
        }
    } );

    // $('.datatable').on( 'click', 'tr.group', function () {
    //     var rowsCollapse = $(this).nextUntil('.group');
    //     $(rowsCollapse).toggleClass('hidden');
    // });

    //Filtre de Recherche Saison et ligne des temps de vol
    // Setup - add a text input to each footer cell
    $('#datatable1 tfoot th').each( function () {
        var titleRechRap = $(this).text();
        if (titleRechRap != "") {
            $(this).html('<input type="text" placeholder="Rech. ' + titleRechRap + '" style="width: 100%;padding: 3px;box-sizing: border-box;"/>');
        }else{
            $(this).html('<div>-</div>');
        }
    } );

    // DataTable
    var tableRechRap = $('#datatable1').DataTable();

    // Apply the search
    tableRechRap.columns().every( function () {
        var thatRechRap = this;

        $( 'input', this.footer() ).on( 'keyup change', function () {
            if ( thatRechRap.search() !== this.value ) {
                thatRechRap
                    .search( this.value )
                    .draw();
            }
        } );
    } );

})(jQuery);

function parseQueryString () {
    var parsedParameters = {},
        uriParameters = location.search.substr(1).split('&');

    for (var i = 0; i < uriParameters.length; i++) {
        var parameter = uriParameters[i].split('=');
        if(!(parameter[0] in parsedParameters)){
            parsedParameters[parameter[0]] = decodeURIComponent(parameter[1]);
        }else{
            parsedParameters[parameter[0]] += "#"+decodeURIComponent(parameter[1]);
        }

    }

    return parsedParameters;
}

