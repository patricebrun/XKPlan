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
        /*dom:    "<'row'<'col-md-6'l><'col-md-6'f>>" +
        "<'row'<'col-md-12'tr>>" +
        "<'row'<'col-md-5'i><'col-md-7'p>>",*/
        bom: true,
        dom: '<"dt-panelmenu clearfix"lBfr>t<"dt-panelfooter clearfix"ip>',
        "order": [],
        "columnDefs": [ {
            "targets": 'no-sort',
            "orderable": false
        } ],
        buttons: [
            {
                extend: 'csvHtml5',
                bom: true,
                //exportOptions: {
                //    columns: [ 0, 1, 2, 3, 4 ]
                //},
                customize: function (csv) {

                    //----------------------------------------------------
                    // Paramêtres des changements à effectuer dasn le CSV
                    //-----------------------------------------------------

                    //les colonnes des datatables que l'on doit exporter en CSV
                    var anumeroscolonnesaafficherparcontroller = {
                        'typeavion': [0, 1, 2, 3, 4],
                        'avion':[0, 1, 2, 3],
                        'aeroport':[0, 1, 2, 3, 4],
                        'ligne':[0,1,2],
                        'typedevol':[0, 1, 2, 3],
                        'naturedevol':[0],
                        'affretement':[0, 1],
                        'compagnie':[0, 1, 2, 3],
                        'pays':[0, 1]
                    };

                    //les changement d'intitulé de légende à faire pour le CSV
                    var libelledetaillearemplacer = {
                        'Version': 'Modéle de type avion',
                        'Libellé': 'Nom'
                    };

                    //-----------------------------------------------
                    //on récupére les index des colonnes à conserver
                    //-----------------------------------------------
                    var currentPath = window.location.pathname;
                    //on enleve le dernier / et le premier / du path
                    currentPath = currentPath.slice(0,eval(currentPath.length-1));
                    currentPath = currentPath.slice(1,eval(currentPath.length));
                    var tmp = currentPath.split("/");
                    var controllername = tmp[eval(tmp.length-1)];

                    //les index de colonne a conserver
                    var aIndexColonnesAConserver = new Array();

                    //if (isset(anumeroscolonnesaafficherparcontroller[controllername])){
                    if (typeof anumeroscolonnesaafficherparcontroller[controllername] !== 'undefined') {
                        aIndexColonnesAConserver = anumeroscolonnesaafficherparcontroller[controllername];
                    }else{
                        aIndexColonnesAConserver = [0,1,2,3,4,5,6,7,8,9,10];
                    }

                    //--------------------------------
                    //on reconstruit le csv customisé
                    //--------------------------------
                    var custom_csv ='';
                    var une_csv_cell_array;
                    //On split le csv sur \n pour récupérer les lignes
                    var csv_row_array = csv.split("\n");

                    //on passe en revue toutes les lignes
                    $.each(csv_row_array, function (indexrow, une_csv_row) {

                        //On split le premier element sur "," pour avoir chaque cellule de légende
                        une_csv_cell_array = une_csv_row.split('","');

                        //on passe en revue toutes les cellules de cette ligne
                        $.each(une_csv_cell_array, function (indexcell, une_csv_cell) {
                            //on enleve le " du premier et dernier element
                            une_csv_cell = une_csv_cell.replace('"', '');
                            //si cette index
                            if(aIndexColonnesAConserver.indexOf(indexcell, 0) != -1){

                                //on remplace les légendes
                                //-------------------------
                                //if(isset(libelledetaillearemplacer[une_csv_cell])){
                                if (typeof libelledetaillearemplacer[une_csv_cell] !== 'undefined') {
                                    custom_csv+='"'+libelledetaillearemplacer[une_csv_cell]+'";';
                                }else{
                                    custom_csv+='"'+une_csv_cell+'";';
                                }
                            }

                        });

                        //on rajoute le saut de ligne
                        custom_csv+='\n';
                        //on enleve la dernière ,
                        custom_csv = custom_csv.replace(',\n', '\n');

                    });

                    //-----------------------------
                    //on retourne le csv customisé
                    //-----------------------------
                    return custom_csv;
                }
            }
        ],
        /*"pageLength": 10,
        "lengthMenu": [ [10, 25, 50, 100, -1], [10, 25, 50, 100, "Tout"] ],*/
        paging: false,
        language: {
            "sProcessing":     "Traitement en cours...",
            "sSearch":         "Rechercher&nbsp;:",
            "lengthMenu":      "Afficher _MENU_ &eacute;l&eacute;ments",
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
        },
    });

})(jQuery);

