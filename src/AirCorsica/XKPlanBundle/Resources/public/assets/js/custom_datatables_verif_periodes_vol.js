'use strict';
//  Author: TemplateMonster.com
//
//  This file is reserved for changes made by the use.
//  Always seperate your work from the theme. It makes
//  modifications, and future theme updates much easier
//
(function($) {
    
    $('#check_all').click(function () {
        var checkBoxes = $(".id_verificateur_vol");
        checkBoxes.prop("checked", !checkBoxes.prop("checked"));
    })


    // Place custom scripts here
    //------------- Data tables -------------//
    //responsive datatables
    $('.datatable').dataTable( {
        /*dom:    "<'row'<'col-md-6'l><'col-md-6'f>>" +
        "<'row'<'col-md-12'tr>>" +
        "<'row'<'col-md-5'i><'col-md-7'p>>",*/
        dom: '<"dt-panelmenu clearfix"lfr>t<"dt-panelfooter clearfix"ip>',
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

})(jQuery);

