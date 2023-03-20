$(function(){

    // Init Select2 - Basic Single
    $(".select2-single").select2({
        language: "fr"
    });

    var $fancyTree = $("#tree").fancytree({
        generateIds: true,
        idPrefix:"",
        "icons":false,
        renderNode: function(event, data) {
            var node = data.node;
            if(-1 != node.title.indexOf('etat-send')){
                var my_class = "mss-default";
            }else if(-1 != node.title.indexOf('etat-cancel')){
                var my_class = "mss-black";
            }else if(-1 != node.title.indexOf('etat-pendingSend')){
                var my_class = "mss-default";
            }else if(-1 != node.title.indexOf('etat-pendingCancel')){
                var my_class = "mss-black";
            }

            $(node.li).find('span.fancytree-node').addClass(my_class);
        }
    });

    $.contextMenu({
       // selector: 'span.contextualMenuHistorique',
        selector: 'span.fancytree-title',
        autoHide: true,
        items: {
            cancelHistorique: {
                name: "Annuler l'historisation de cet élément",
                callback: function(key, opt){
                    var elm = opt.$trigger.children();
                    var idVol  =  elm.data('idvol');
                    var idTree =  elm.closest('li.fancytree-lastsib').attr('id');
                    // Get the DynaTree object instance
                    var tree = $fancyTree.fancytree("getTree");
                    //Get the node
                    var node = tree.getNodeByKey(idTree);
                    $.ajax({
                        type: 'get',
                        url: Routing.generate('vol_ajaxcancelhistoriqueAction', { id:  idVol}),
                        success: function (data) {
                            console.log(data);
                            node.removeChildren();
                        }
                    });

                },
                disabled: function(key, opt){
                    var elm = opt.$trigger.children();
                    if(!elm.hasClass('cancel-historique')){
                        return true;
                    }
                }
            }
        }
    });


});
