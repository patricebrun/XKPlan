var $collectionHolder = $('#aircorsica_xkplanbundle_avion_periodesImmobilsation');
var $addTagLink = $('.add_tag_link');
// var $removeTagLink = $('.remove_immo');

jQuery(document).ready(function() {

    $collectionHolder.data('index', $collectionHolder.find('table tr').length) - 1;

    $addTagLink.on('click', function(e) {
        e.preventDefault();
        addTagForm($collectionHolder);
    });

    $(document).on('click','.remove_immo', function(e) {
        e.preventDefault();
        $(e.target).closest('tr').remove();
    });




});


//function addTagForm($collectionHolder, $newLinkLi) {
function addTagForm($collectionHolder) {
    // Get the data-prototype explained earlier
    var prototype = $collectionHolder.data('prototype');


    // get the new index
    // recherche le dernier index de la liste+1 evite le bug avec l'utilisation de .length si on supprime des elements
    if($('tr.wrapperDatePicker').last().find('input').length!=0) {
        var derniertagformid = $('tr.wrapperDatePicker').last().find('input').attr('id');
        var derniertagformidtmparr = derniertagformid.split("_");
        var index = eval(derniertagformidtmparr[4]) + 1;
    }else{
        var index = $collectionHolder.find('table tr').length;
    }

    // Replace '__name__' in the prototype's HTML to
    // instead be a number based on how many items we have
    var newForm = prototype.replace(/__name__/g, index);


    $collectionHolder.find('table tbody').append(newForm);



}