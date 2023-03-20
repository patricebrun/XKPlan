var options =
    {
        changeMonth: true,
        changeYear: true,
        monthNames: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],
        monthNamesShort: ['Janv.', 'Févr.', 'Mars', 'Avril', 'Mai', 'Juin', 'Juil.', 'Août', 'Sept.', 'Oct.', 'Nov.', 'Déc.'],
        dayNames: ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'],
        dayNamesShort: ['Dim.', 'Lun.', 'Mar.', 'Mer.', 'Jeu.', 'Ven.', 'Sam.'],
        dayNamesMin: ['D', 'L', 'M', 'M', 'J', 'V', 'S'],
        dateFormat: 'dd-mm-yy',
        showWeek: true,
        firstDay: 1,
        closeText: 'Fermer',
        currentText: 'Aujourd\'hui',
        prevText: '<i class="fa fa-chevron-left"></i>',
        nextText: '<i class="fa fa-chevron-right"></i>',
        showButtonPanel: true
    }

/**
 *	Cette fonction permet d'initialiser des datepickers par couples.
 * 	Les champs inputs réprésentant les datepickers doivent être contenus
 *	dans un conteneur (wrapper) ayant la classe wrapperDatePicker.
 *
 *	var opts : 	Ce paramètre est optionel. Il doit être un objet Javascript contenant toutes
 *	les options à passer aux datepickers.
 *
 *	var wrapper : Ce paramètre est optionnel. Dans le cas ou il est renseigné,
 *	il doit être un objet jQuery englobant un wrapper ou un groupe de wrappers.
 *	La fonction n'initialisera que les datepickers présent dans le ou les wrappers
 *	renseignés. Sinon elle initialisera tous les datepickers présent sur la page.
 *	Dans le cas ce paramètre est renseigné, la paramètre des options doit l'etre
 *	également, si aucun option ne doit être renseignée il est possible de renseigné
 *	undefined. Ex : init_datepicker(undefined, wrapper) ou init_datepicker({}, wrapper)
 *	ou encore init_datepicker init_datepicker(null, wrapper).
 **/
function init_datepicker(opts, wrapper)
{
    // Sont les options passées au datepickers
    var options = opts || {};
    // Stocke tous les wrappers présents sur la page
    var wrappers = wrapper || $('.wrapperDatePicker');

    // Pour chaque wrapper..
    wrappers.each(function(index)
    {
        // 1 - Déclarations / initialisation ----------------------------------------------------------

        // Est le wrapper dans lequel on va récupérer les datepickers
        // /!\ this fait ici référence à un wrapper /!\
        var currentWrapper = $(this);
        // Est le datepicker de la date de début
        var dateDebut = currentWrapper.find('input[data-type="debut"]');
        // Est le datepicker de la date de fin
        var dateFin = currentWrapper.find('input[data-type="fin"]');
        // Est l'attribut onSelect du datepicker. Cette attribut permet d'exécuter une
        // callback lorsqu'une date est sélectionnée
        var onSelect = null;
        // Sont les options passé au datepicker.
        var currentOptions = options;
        // Sert a stocker une date si celle-ci est déja présente avant l'initialisation du datepicker
        var value = "";

        // Ces propriétés sont mises à null pour éviter des propagations non désirées dans les autres
        // datepickers.
        currentOptions.minDate = null;
        currentOptions.maxDate = null;

        // 2 - Construction date de début -------------------------------------------------------------

        // On vérifie si des valeurs sont déja présentes dans le champs de saisie de la date de fin
        value = dateFin.val();

        // Si c'est le cas on met à jour la propriété maxDate du datepicker de la date de début
        if(value !== "")
        {
            currentOptions.minDate = null;
            currentOptions.maxDate = value;
        }

        // Définition de la callback utilisé pour la date de début
        onSelect = function()
        {
            // Récupération de la date sélectionnée
            // /!\ this fait ici référence à l'input du datepicker /!\
            var selectedDate = $(this).datepicker('getDate');
            // Définiton de la date maximale pour le datepicker de la date de fin
            dateFin.datepicker('option', 'minDate', selectedDate);
            $(this).change();
        }

        // Ajout de la callback aux options
        currentOptions.onSelect = onSelect;
        // Initialisation du datepicker de la date de début
        dateDebut.datepicker(currentOptions);

        // 3 - Construction date de fin ---------------------------------------------------------------

        // On vérifie si des valeurs sont déja présentes dans le champs de saisie de la date de début
        value = dateDebut.val();

        // Si c'est le cas on met à jour la propriété maxDate du datepicker de la date de fin
        if(value !== "")
        {
            currentOptions.minDate = value;
            currentOptions.maxDate = null;
        }

        // Définition de la callback utilisé pour la date de fin
        onSelect = function()
        {
            // Récupération de la date sélectionnée
            // /!\ this fait ici référence à l'input du datepicker /!\
            var selectedDate = $(this).datepicker('getDate');
            // Définiton de la date maximale pour le datepicker de la date de fin
            dateDebut.datepicker('option', 'maxDate', selectedDate);
            $(this).change();
        }

        // Ajout de la callback aux options
        currentOptions.onSelect = onSelect;
        // Initialisation du datepicker de la date de début
        dateFin.datepicker(currentOptions);

    });
}

/*
 *	Cette fonction permet de détruire tous les datepickers. Les inputs ne se comporteront plus
 *	comme des champs permettant de sélectionner des dates. Cette fonction supprime également la
 *	div automatiquement créée par la librairie datepicker, qui a l'ID ui-datepicker-div.
 *
 *  var wrapper : ce paramètre permet de spécifier un wrapper dans lequel ira travailler la fonction.
 *	Si ce paramètre n'est pas renseigné la fonction travaillera dans tous les wrappers présents
 *	sur la page.
 */
function destroy_datepicker(wrappers)
{
    // Stocke tous les wrappers présents sur la page.
    var wrappers = wrappers || $('.wrapperDatePicker');

    // Pour chaque wrapper..
    wrappers.each(function(index)
    {
        // Stocke l'ensemble des datepickers présents dans le wrappers.
        var datepickers = $(this).find('input.datepicker');

        // Pour chaque datepicker..
        datepickers.each(function(index)
        {
            // On supprime datepicker de cette input
            $(this).datepicker('destroy');
            // On remet à zéro la valeur de cette input
            // $(this).val("");
        });
    });


    // Enfin on détruit la div ayant l'ID ui-datepicker-div
    $('body #ui-datepicker-div').remove();

    //console.log('DESTROY ok');
}

function opened_modal()
{
    // Stocke le wrapper de la modal
    var wrapper_modal = $('.wrapperDatePicker-modal');

    if(wrapper_modal){
        // Supression de tous les datepickers présent dans le wrapperDatePicker.
        destroy_datepicker(wrapper_modal);
        // Création des datepickers de la modal
        init_datepicker(options, wrapper_modal);
    }

    //console.log('INIT MODAL ok');
}

function closed_modal()
{
    // Stocke le wrapper de la modal
    var wrapper_modal = $('.wrapperDatePicker-modal');

    if(wrapper_modal){
        // Détruit les datepickers de la modal
        destroy_datepicker(wrapper_modal);
        // Initialise tous les datepickers de la page.
        init_datepicker(options);
    }

    //console.log('CLOSE MODAL ok');
}
