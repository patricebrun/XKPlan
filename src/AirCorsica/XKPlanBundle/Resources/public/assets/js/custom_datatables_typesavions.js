/**
 * Created by JeanJo on 05/09/2017.
 */
'use strict';
//  Author: TemplateMonster.com
//
//  This file is reserved for changes made by the use.
//  Always seperate your work from the theme. It makes
//  modifications, and future theme updates much easier
//
(function($) {

    // Place custom scripts here
    //------------- Data tables -------------//
    //responsive datatables
    // Init DataTables
    var table = $('.datatable').dataTable({
        bom: true,
        dom: '<"dt-panelmenu clearfix"lBfr>t<"dt-panelfooter clearfix"ip>',
        // buttons: [
        //         'csv',


            //a decomenter pour avoir une generation CSV custom
            //{
            //    extend: 'csvHtml5',
            //    action: function ( e, dt, node, config ) {
            //        bootbox.confirm({
            //            message: 'Si vous confirmez cette action, veuillez patienter pendant la génération du Listing Types Avions au format CSV. Le fichier sera automatiquement téléchargé sur votre ordinatuer.',
            //            buttons: {
            //                'cancel': {
            //                    label: 'Annuler',
            //                    className: 'btn-default pull-left'
            //                },
            //                'confirm': {
            //                    label: 'Confirmer',
            //                    className: 'btn-danger pull-right'
            //                }
            //            },
            //            callback: function(result) {
            //                if(result){
            //                    window.location.href = Routing.generate('typeavion_typeaviontocsv');
            //                }
            //            }
            //        });
            //    }
            //},
        // ],
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
            });
        },
        fieldSeparator : ";",
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
        },
        "order": [],
        "columnDefs": [ {
            "targets": 'no-sort',
            "orderable": false
        } ],

    });


})(jQuery);
